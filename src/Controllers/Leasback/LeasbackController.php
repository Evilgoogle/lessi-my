<?php

/**
 * This file is part of Lessi project.
 * © byvlad, 2022
 */

namespace App\Controllers\Leasback;

use App\Models\LeasBack\LeasBack;
use Slim\Http\Request;
use App\Models\MACS\Fahrzeughistorie;
use App\Models\MACS\Auftrag;
use App\Models\MACS\Faht;
use App\Models\MACS\Fahrzeug;
use App\Models\LeasBack\Fahrzeug as LeasFahrzeug;
use App\Models\LeasBack\Auftrag as AttachAuftrag;
use Illuminate\Database\Capsule\Manager as DB;
use App\Models\LeasBack\Step;
use App\Models\LeasBack\Junge\Fahrzeug as JungeFahrzeug;
use App\Models\LeasBack\FahrzeugJunge;
use App\Controllers\Leasback\StepController;
use App\Models\LeasBack\StepStatus;
use App\Models\LeasBack\DirektannahmeTags;
use App\Models\LeasBack\DirektannahmeInspektion;

class LeasbackController extends \App\Controllers\Controller
{
    private function stepInit($leasback) 
    {
        $isset = StepStatus::where('leasback_id', $leasback->id)->first();
        
        if(!isset($isset)) {
            $step = new StepController($this->container);
            $step->init($leasback);
        }
    }
    
    private function number_normalize($carNumber) 
    {
	$str1 = preg_replace("/[0-9- ]/ui", "", $carNumber);
        $str2 = strtoupper(preg_replace("/[a-zA-Z- ]/ui", "", $carNumber));
        $v1_number = substr($str1,0,2) . "-" . substr($str1,2) . " " . $str2;
        $v1_number = ($str2 == '') ? $carNumber : $v1_number;
        $v1_number = strtoupper($v1_number);
	
	return $v1_number;
    }

    public function index(Request $request, $response)
    {
        if(!$this->Auth->user()->priviliges()->contains('leasback.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $leasing = LeasBack::orderBy('dt', 'ASC')->get();
        $data = [];
        foreach($leasing as $item) {
            $this->stepInit($item);
            
            $data[$item->id]['lb'] = $item;

	    $fahrzeug = LeasFahrzeug::where('leasback_id', $item->id)->first();
	    if(isset($fahrzeug)) {
		$data[$item->id]['isset'] = 'ok';
		$autfrag = isset($fahrzeug) ? Auftrag::find($fahrzeug->AUFTRAGID) : null;
	    } else {
		$data[$item->id]['isset'] = 'no';
		$fahrzeug = Fahrzeughistorie::getCarInfoByNumber($this->number_normalize($item->mark));

		$autfrag = null;
                if(isset($fahrzeug)) {
                    $autfrag = Auftrag::join('KUNDE as k', 'k.KUNDEID', '=', 'AUFTRAG.KUNDEID')
                        ->where('FAHRZEUGID', $fahrzeug->FAHRZEUGID)
                        ->where('k.KUNDEID', '<>', '30')
                        ->orderBy('AUFTRAG.ANNAHME', 'DESC')
                        ->first();
                }
	    }
	    
	    $attach_auftrags = AttachAuftrag::where('leasback_id', $item->id)->first();
            
            $attach_fahrzeug_junge = FahrzeugJunge::where('leasback_id', $item->id)->first();
	    
	    $data[$item->id]['autfrag'] = isset($autfrag) ? $autfrag : null;
	    $data[$item->id]['fahrzeug'] = isset($fahrzeug) ? $fahrzeug : null;
	    $data[$item->id]['attach_auftrag'] = isset($attach_auftrags) ? $attach_auftrags : null;
            $data[$item->id]['attach_fahrzeug_junge'] = isset($attach_fahrzeug_junge) ? $attach_fahrzeug_junge : null;
            
            $data[$item->id]['steps'] = StepStatus::where('leasback_id', $item->id)->get();
	    
            /*if(!is_null($fahrzeug)) {
                $autfrag = Auftrag::find($fahrzeug->AUFTRAGID);
                $autfrag->FAHRGESTELLNUMMER = $fahrzeug->FAHRGESTELLNUMMER;
                $autfrag->KMSTAND = $fahrzeug->KMSTAND;
                $autfrag->ERSTZULASSUNG = $fahrzeug->ERSTZULASSUNG;
                $autfrag->FZSTATUSTEXT = $fahrzeug->FZSTATUSTEXT;
                $data[$item->id]['autfrag'] = $autfrag;
                $data[$item->id]['fahrzeug'] = $fahrzeug;
            }*/
        }

        $this->view->render($response, 'leasback/index.twig', compact('data'));
    }
    
    public function step($request, $response, $arg) 
    {
        if(!StepController::check_access($this->Auth->user(), $arg['step'])) {
	    return $response->withRedirect($this->router->pathFor('errors.403'));
	}
        
        $leasback = LeasBack::find($arg['id']);
        if(!isset($leasback)) {
            return $response->withRedirect($this->router->pathFor('errors.404'));
        }
        
        $fahrzeug['source'] = 'leasback';
        $fahrzeug['item'] = LeasFahrzeug::where('leasback_id', $leasback->id)->first();
        if(isset($fahrzeug['item'])) {
            $fahrzeug['autfrag'] = isset($fahrzeug['item']) ? Auftrag::find($fahrzeug['item']->AUFTRAGID) : null;
        } else {
            $fahrzeug['source'] = 'macs';
            $fahrzeug['item'] = Fahrzeughistorie::getCarInfoByNumber($this->number_normalize($leasback->mark));

            $fahrzeug['autfrag'] = null;
            if(isset($fahrzeug['item'])) {
                $fahrzeug['autfrag'] = Auftrag::join('KUNDE as k', 'k.KUNDEID', '=', 'AUFTRAG.KUNDEID')
                    ->where('FAHRZEUGID', $fahrzeug['item']->FAHRZEUGID)
                    ->where('k.KUNDEID', '<>', '30')
                    ->orderBy('AUFTRAG.ANNAHME', 'DESC')
                    ->first();
            }
        }

        $auftrags = AttachAuftrag::where('leasback_id', $leasback->id)->first();
        $fahrzeug_junge = FahrzeugJunge::where('leasback_id', $leasback->id)->first();
        
        $steps = StepStatus::where('leasback_id', $arg['id'])->get();
        $steps_data = [];
        if($arg['step'] == 'direktannahme') {
            $isset_schaden = DirektannahmeTags::where('leasback_id', $leasback->id)->where('type', 'schaden')->get();
            if($isset_schaden->count() == 0) {
                DirektannahmeTags::init($leasback->id, 'schaden');
            }
            $isset_vorschaden = DirektannahmeTags::where('leasback_id', $leasback->id)->where('type', 'vorschaden')->get();
            if($isset_vorschaden->count() == 0) {
                DirektannahmeTags::init($leasback->id, 'vorschaden');
            }
            $isset_inspektion = DirektannahmeInspektion::where('leasback_id', $leasback->id)->first();
            if(!isset($isset_inspektion)) {
                DirektannahmeInspektion::init($leasback->id);
            }
            
            $steps_data['direktannahme']['schaden'] = DirektannahmeTags::where('leasback_id', $leasback->id)->where('type', 'schaden')->get();
            $steps_data['direktannahme']['vorschaden'] = DirektannahmeTags::where('leasback_id', $leasback->id)->where('type', 'vorschaden')->get();
            $steps_data['direktannahme']['inspektion'] = DirektannahmeInspektion::where('leasback_id', $leasback->id)->first();
        }
        
        $current = '';
        foreach ($steps as $s) {
            if($s->step == $arg['step']) {
                $current = $s;
            }
        }
        
        $this->view->render($response, 'leasback/step/' . $arg['step'] . '.twig', compact('steps', 'current', 'leasback', 'fahrzeug', 'auftrags', 'fahrzeug_junge', 'steps_data'));
    }

    public function search_fahrzeug(Request $request, $response)
    {
	$string = strtolower($request->getParam('string') ?: '');
        
        if($request->getParam('get_fahrzeug') == 'macs') {
            /*$results = Fahrzeug::select('FAHRZEUG.FAHRZEUGID', 'FAHRZEUG.FAHRGESTELLNUMMER', 'FAHRZEUG.MODELLTEXT', 'FAHRZEUGHISTORIE.POLKENNZEICHEN')
                    ->where(['FAHRZEUG.MANDANTID' => 1])
                    ->selectRaw('FAHRZEUG.*, FAHRZEUGHISTORIE.AUFTRAGID, FAHRZEUGHISTORIE.KMSTAND, FZSTATUS.FZSTATUSTEXT')
                    ->leftJoin('FAHRZEUGHISTORIE', 'FAHRZEUGHISTORIE.FAHRZEUGHISTORIEID', 'FAHRZEUG.FAHRZEUGHISTORIEIDAKTUELL')
                    ->leftJoin('FZSTATUS', 'FZSTATUS.FZSTATUSID', 'FAHRZEUG.FZSTATUSID')
                    ->limit(5)
                    ->where(function ($q) use ($string)
                        {
                            //$q->where("FAHRZEUGHISTORIE.POLKENNZEICHEN", 'LIKE', '%'.$this->number_normalize($string).'%')
                            $q->orwhere('FAHRZEUG.FAHRGESTELLNUMMER', 'like', '%' . $string . '%');
                            //->orwhere('ka.NAME1', 'like', '%' . $string . '%');
                        })
                    ->get();*/

            $results = DB::connection('macs')->select(DB::raw('select "FAHRZEUG"."FAHRZEUGID", "FAHRZEUG"."FAHRGESTELLNUMMER", "FAHRZEUG"."MODELLTEXT", "FAHRZEUGHISTORIE"."POLKENNZEICHEN",
                FAHRZEUG.*, FAHRZEUGHISTORIE.AUFTRAGID, FAHRZEUGHISTORIE.KMSTAND, FZSTATUS.FZSTATUSTEXT
                from "FAHRZEUG"
                left join "FAHRZEUGHISTORIE" on "FAHRZEUGHISTORIE"."FAHRZEUGHISTORIEID" = "FAHRZEUG"."FAHRZEUGHISTORIEIDAKTUELL"
                left join "FZSTATUS" on "FZSTATUS"."FZSTATUSID" = "FAHRZEUG"."FZSTATUSID"
                where ("FAHRZEUG"."MANDANTID" = 1)
                and (
                "FAHRZEUGHISTORIE"."POLKENNZEICHEN" LIKE \'%'.$this->number_normalize($string).'%\'
                or "FAHRZEUG"."FAHRGESTELLNUMMER" LIKE \'%'.strtoupper($string).'%\'
                ) ROWS 5'));

            if(count($results) == 0) {
                $kundes = \App\Models\MACS\Kunde::select('KUNDE.KUNDEID')
                    ->join('ADRESSE as ad', 'ad.ADRESSEID', '=', 'KUNDE.ADRESSEID')
                    ->where('ad.NAME1', 'LIKE', '%'.$request->getParam('string').'%')
                    ->get();

                $results = [];
                foreach($kundes as $k) {
                    $auftrags = Auftrag::select('f.FAHRZEUGID', 'f.FAHRGESTELLNUMMER', 'f.MODELLTEXT', 'ad.NAME1 as KUNDENAME')
                        ->join('FAHRZEUG as f', 'f.FAHRZEUGID', '=', 'AUFTRAG.FAHRZEUGID')
                        ->join('KUNDE as k', 'k.KUNDEID', '=', 'AUFTRAG.KUNDEID')
                        ->join('ADRESSE as ad', 'ad.ADRESSEID', '=', 'k.ADRESSEID')
                        ->where('k.KUNDEID', $k->KUNDEID)
                        ->first();

                    if(isset($auftrags)) {
                        $results[] = (object)[
                            'FAHRZEUGID' => $auftrags->FAHRZEUGID,
                            'FAHRGESTELLNUMMER' => $auftrags->FAHRGESTELLNUMMER,
                            'MODELLTEXT' => $auftrags->MODELLTEXT,
                            'POLKENNZEICHEN' => '',
                            'POLKENNZEICHEN_TEXT' => '',
                            'KUNDENAME' => $auftrags->KUNDENAME
                        ];
                    }
                }
            }

            $collect = [];
            foreach($results as $r) {
                $collect[] = [
                    'FAHRZEUGID' => $r->FAHRZEUGID,
                    'FAHRGESTELLNUMMER' => $r->FAHRGESTELLNUMMER,
                    'MODELLTEXT' => $this->mb->convert($r->MODELLTEXT),
                    'POLKENNZEICHEN' => $this->mb->convert($r->POLKENNZEICHEN),
                    'POLKENNZEICHEN_TEXT' => $string,
                    'KUNDENAME' => isset($r->KUNDENAME) ? $this->mb->convert($r->KUNDENAME) : ''
                ];
            }
            
        } elseif($request->getParam('get_fahrzeug') == 'junge') {
            
            $results = JungeFahrzeug::search($string);
            
            $collect = [];
            foreach($results as $r) {
                $collect[] = [
                    'FAHRZEUGID' => $r->id,
                    'FAHRGESTELLNUMMER' => $r->fin,
                    'MODELLTEXT' => $r->model.' '.$r->mark,
                    'POLKENNZEICHEN' => '',
                    'POLKENNZEICHEN_TEXT' => '',
                    'KUNDENAME' => $r->customer_name
                ];
            }
        }

        return $response->withJson(collect($collect));
    }
    
    public function attach_fahrzeug(Request $request, $response) 
    {
	$id = $request->getParam('id');
        
        if($request->getParam('get_fahrzeug') == 'macs') {
	
            $fahrzeug = Fahrzeug::leftJoin('FAHRZEUGHISTORIE', 'FAHRZEUGHISTORIE.FAHRZEUGHISTORIEID', 'FAHRZEUG.FAHRZEUGHISTORIEIDAKTUELL')
                    ->where('FAHRZEUG.FAHRZEUGID', $id)
                    ->first();
            //$autfrag = isset($fahrzeug) ? Auftrag::find($fahrzeug->AUFTRAGID) : null;
            $autfrag = null;
            if(isset($fahrzeug)) {
                $autfrag = Auftrag::join('KUNDE as k', 'k.KUNDEID', '=', 'AUFTRAG.KUNDEID')
                    ->where('FAHRZEUGID', $fahrzeug->FAHRZEUGID)
                    ->where('k.KUNDEID', '<>', '30')
                    ->orderBy('AUFTRAG.ANNAHME', 'DESC')
                    ->first();
            }

            if(isset($fahrzeug)) {
                $set = LeasFahrzeug::where('leasback_id', $request->getParam('leasback_id'))->first();
                if(!isset($set)) {
                    $set = new LeasFahrzeug();
                }

                $set->leasback_id = $request->getParam('leasback_id');
                $set->FAHRZEUGID = $fahrzeug->FAHRZEUGID;
                $set->FZSTATUSTEXT = $fahrzeug->FZSTATUSTEXT;
                $set->FAHRZEUGARTTEXT = $this->mb->convert($fahrzeug->fzh_actuell->art->FAHRZEUGARTTEXT);
                $set->FAHRGESTELLNUMMER = $fahrzeug->FAHRGESTELLNUMMER;
                $set->KMSTAND = $fahrzeug->KMSTAND;
                $set->ERSTZULASSUNG = $fahrzeug->ERSTZULASSUNG;
                $set->KUNDENNAME = isset($autfrag) ? $this->mb->convert($autfrag->kunde->adresse->NAME1) : null;
                $set->KUNDENNR = isset($autfrag) ? $autfrag->kunde->KUNDENNR : null;
                $set->KURZNAME = isset($autfrag) ? $this->mb->convert($autfrag->kunde->adresse->KURZNAME) : null;
                $set->MITARBEITERNAME = isset($autfrag) ? isset($autfrag->mitarbeiter) ? $this->mb->convert($autfrag->mitarbeiter->adresse->NAME1) : null : null;
                $set->MITARBEITERKURZNAME = isset($autfrag) ? isset($autfrag->mitarbeiter) ? $this->mb->convert($autfrag->mitarbeiter->adresse->KURZNAME) : null : null;
                $set->save();

                return $response->withJson('ok');
            }
        } elseif($request->getParam('get_fahrzeug') == 'junge') {
            
            $fahrzeug = JungeFahrzeug::find($id);
            
            if(isset($fahrzeug)) {
                $set = FahrzeugJunge::where('leasback_id', $request->getParam('leasback_id'))->first();
                if(!isset($set)) {
                    $set = new FahrzeugJunge();
                }
                $set->leasback_id = $request->getParam('leasback_id');
                $set->seller = $fahrzeug->seller;
                $set->mbg = $fahrzeug->mbg;
                $set->deposit = $fahrzeug->deposit;
                $set->residual = $fahrzeug->residual;
                $set->saldo = $fahrzeug->saldo;
                $set->final_maturity_at = $fahrzeug->final_maturity_at;
                $set->model = $fahrzeug->model;
                $set->mark = $fahrzeug->mark;
                $set->fin = $fahrzeug->fin;
                $set->approval_ln_at = $fahrzeug->approval_ln_at;
                $set->customer_type = $fahrzeug->customer_type;
                $set->customer_name = $fahrzeug->customer_name;
                $set->save();
                        
                return $response->withJson('ok');
            }
        }
    }

    public function get_auftrags(Request $request, $response)
    {
	$item = LeasBack::find($request->getParam('id'));
	
	$auftrags = [];
	
	$fahrzeug = LeasFahrzeug::where('leasback_id', $item->id)->first();
	if(!isset($fahrzeug)) {
	    $fahrzeug = Fahrzeughistorie::getCarInfoByNumber($this->number_normalize($item->mark));
	} else {
	    $fahrzeug = Fahrzeug::where('FAHRZEUGID', $fahrzeug->FAHRZEUGID)->first();
	}
	
	if(isset($fahrzeug)) {
	    $auftrags = $fahrzeug->auftrags()->orderBy('ANNAHME', 'DESC')->get();
	}
	
	$template = $this->view->fetch('leasback/templates/auftrags.twig', ['auftrags' => $auftrags]);
	
	return $response->withJson($template);
    }

    public function attach_auftrag(Request $request, $response) 
    {
	$leasback_id = $request->getParam('leasback_id');
	$auftrag_id = $request->getParam('auftrag_id');
	
	$auftrag = Auftrag::where('AUFTRAGID', $auftrag_id)->first();
	if(isset($auftrag)) {
	    $set = AttachAuftrag::where('leasback_id', $leasback_id)->first();
	    if(!isset($set)) {
		$set = new AttachAuftrag();
	    }
	    $set->leasback_id = $leasback_id;
	    $set->AUFTRAGSNR = $auftrag->AUFTRAGSNR;
	    $set->STATUS = $auftrag->status->BEZEICHNUNG;
	    $set->TYP = $auftrag->typ->BEZEICHNUNG;
	    $set->ANLIEFERUNG = $auftrag->ANLIEFERUNG;
	    $set->ANNAHME = $auftrag->ANNAHME;
	    $set->VERMERK = $auftrag->VERMERK;
	    $set->AUFTRAGID = $this->mb->convert($auftrag->AUFTRAGID);
	    $set->save();
	    
	    return $response->withJson('ok');
	}
    }

    public function addAppointment(Request $request, $response)
    {
        $data = $request->getParams();
        $data['leasing_from_site'] = 'leasback';
        LeasBack::create($data);

        return $response->withRedirect($this->router->pathFor('leasback.index'));
    }
}
