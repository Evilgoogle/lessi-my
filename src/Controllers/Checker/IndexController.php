<?php
namespace App\Controllers\Checker;

use App\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use App\Models\MACS;
use App\Models\Checker\Auftrag;
use App\Models\Checker\Fahrzeug;
use App\Models\Checker\Kunde;
use App\Models\Checker\KundeComm;
use App\Models\Checker\KundeDSE;
use App\Models\Checker\AuftragPosition;
use App\Models\Checker\AuftragKundenannahmeKundenhinweise;
use App\Models\Checker\AuftragKundenannahmeFrzg;
use App\Controllers\Checker\Step\Kundenannahme\Infos;
use App\Models\Checker\Filemanager;
use App\Models\Checker\AuftragDiagnosereparaturKundenbeanstandung;
use App\Models\Checker\AuftragDiagnosereparaturDokumentation;
use App\Models\Checker\AuftragReparaturabnahmeProbefahrt;
use App\Models\Checker\AuftragReparaturabnahmeDokumentation;
use App\Models\Checker\KundeDatenschutz;
use App\Models\Checker\Tasks;
use App\Models\Checker\TasksDataTechnischeHinweise;
use App\Controllers\Checker\Libs\Helpers;
use App\Controllers\Checker\Tasks\Queries;
use App\Models\Checker\AuftragAddons;
use App\Controllers\Checker\Tasks\History;
use App\Models\Checker\GarantieKulanzantrag;

class IndexController extends Controller
{

    function list($request, $response)
    {
        $this->checkPermission($request, $response);

        $auftrage = \App\Models\Checker\Auftrag::orderBy('created_at', 'DESC')->get();

        $notice = [
            'success' => null,
            'is_numeric' => null,
            'not_found' => null,
            'exists' => null,
            'fahrzeug_not_found' => null
        ];
        $messages = $this->flash->getMessages();
        if(array_key_exists('success', $messages)) {
            $notice['success'] = $messages['success'][0];
        }
        if(array_key_exists('is_numeric', $messages)) {
            $notice['is_numeric'] = $messages['is_numeric'][0];
        }
        if(array_key_exists('not_found', $messages)) {
            $notice['not_found'] = $messages['not_found'][0];
        }
        if(array_key_exists('exists', $messages)) {
            $notice['exists'] = $messages['exists'][0];
        }
        if(array_key_exists('fahrzeug_not_found', $messages)) {
            $notice['fahrzeug_not_found'] = $messages['fahrzeug_not_found'][0];
        }

        $this->view->render($response, 'checker/index.twig', ['auftrage' => $auftrage, 'notice' => $notice]);
    }

    public function search($request, $response)
    {
        $string = strtolower($request->getParam('string') ?: '');

        $results = MACS\Auftrag::search($string);

        $collect = [];
        foreach($results as $r) {
            $collect[] = [
                'AUFTRAGID' => $r->AUFTRAGID,
                'AUFTRAGSNR' => $r->AUFTRAGSNR,
                'FAHRGESTELLNUMMER' => $r->FAHRGESTELLNUMMER,
                'NAME1' => $this->mb->convert($r->NAME1)
            ];
        }

        return $response->withJson(collect($collect));
    }

    public function add($request, $response)
    {
        $this->checkPermission($request, $response);

        $attack_id = $request->getParam('attach_id');
        if($request->getParam('attach_id') == '') {
            $attack_id = $request->getParam('string');
        }

        if(!is_numeric($attack_id)) {

            $this->flash->addMessage('is_numeric', 'Ungültige Auftragsnummer');
            return $response->withRedirect($this->router->pathFor('checker.list'));
        }

        $auftrag = MACS\Auftrag::where('AUFTRAGSNR', $attack_id)->first();
        if(!isset($auftrag)) {

            $this->flash->addMessage('not_found', 'Auftrag nicht gefunden');
            return $response->withRedirect($this->router->pathFor('checker.list'));
        } else {

            $test_auftrag_site = Auftrag::where('auftragnr', $auftrag->AUFTRAGSNR)->first();
            if(isset($test_auftrag_site)) {

                $this->flash->addMessage('exists', 'Auftrag ist bereits vorhanden');
                return $response->withRedirect($this->router->pathFor('checker.list'));
            } else {

                $nr = $attack_id;
                if ($auftrag = MACS\Auftrag::where('AUFTRAGSNR', $nr)->where('ART', '!=', 4)->first())
                {
                    if (!Auftrag::find($auftrag->AUFTRAGID))
                    {
                        if(isset($auftrag->FAHRZEUGID)) {
                            $this->createFahrzeugFromMACS($auftrag);
                            $this->createKundeFromMACS($auftrag);

                            // ----
                            $netto	 = 0;
                            $brutto	 = 0;

                            foreach ($auftrag->rechnungs as $rechnung)
                            {
                                $netto	 += $rechnung->NETTOBETRAG;
                                $brutto	 += $rechnung->BRUTTOBETRAG;
                            }

                            $dAuftrag		 = new Auftrag();
                            $dAuftrag->id		 = $auftrag->AUFTRAGID;
                            $dAuftrag->fahrzeug_id	 = $auftrag->FAHRZEUGID;
                            $dAuftrag->kunde_id	 = $auftrag->KUNDEID;
                            $dAuftrag->auftragnr	 = $auftrag->AUFTRAGSNR;
                            $dAuftrag->date		 = $auftrag->ANNAHME;
                            $dAuftrag->type		 = Auftrag::TYPE[$auftrag->TYP];

                            $dAuftrag->anlieferung		 = $auftrag->ANLIEFERUNG; //fz anlieferung
                            $dAuftrag->fertigstellung	 = $auftrag->FERTIGPLAN; //Fertigstellung
                            $dAuftrag->created_at = date('Y-m-d h:i:s');
                            $dAuftrag->save();

                            // ----
                            $this->stepInit($auftrag);
                            $this->createKundenhinweise($auftrag);
                            $this->createFrzg($auftrag);
                            $this->updatePositionsFromMACS($auftrag);
                            $this->createDokumentation($auftrag);
                            $this->createProbefahrt($auftrag);
                            $this->createReparaturabnahmeDokumentation($auftrag);

                            $this->flash->addMessage('success', 'Auftrag hinzugefügt');
                        } else {
                            $this->flash->addMessage('fahrzeug_not_found', 'Fahrzeug nicht gefunden (ID ist null)');
                            return $response->withRedirect($this->router->pathFor('checker.list'));
                        }
                    }
                }
                return $response->withRedirect($this->router->pathFor('checker.list'));
            }
        }
    }

    public function attach($request, $response)
    {
        $this->checkPermission($request, $response);

        $get_picks = json_decode($request->getParam('pick'));
        $auftrag = false;
        $errors = [];

        foreach($get_picks as $pick) {
            $auftrag = MACS\Auftrag::where('AUFTRAGSNR', $pick)->first();
            if(!isset($auftrag)) {

                $errors[] = 'Auftrag nicht gefunden';
            } else {

                $test_auftrag_site = Auftrag::where('auftragnr', $auftrag->AUFTRAGSNR)->first();
                if(isset($test_auftrag_site)) {

                    $errors[] = 'Bereits hinzugefügt';
                } else {

                    $nr = $pick;
                    if ($auftrag = MACS\Auftrag::where('AUFTRAGSNR', $nr)->where('ART', '!=', 4)->first())
                    {
                        if (!Auftrag::find($auftrag->AUFTRAGID))
                        {
                            if(!isset($auftrag->FAHRZEUGID)) {
                                $errors[] = 'Fahrzeug nicht gefunden (ID ist null)';
                            }
                        }
                    }
                }
            }
        }

        if(count($errors) == 0) {
            if(count($get_picks) == 0) {
                return $response->withJson(['nichts ausgewählt'], 422);
            }

            foreach($get_picks as $pick) {
                $isset = AuftragAddons::where('auftragnr', $pick)->first();
                if(isset($isset)) {
                    return $response->withJson(['Auftrag '.$pick.' - chon hinzugefügt'], 422);
                }

                $auftrag = MACS\Auftrag::where('AUFTRAGSNR', $pick)->first();

                $netto = 0;
                $brutto = 0;

                foreach ($auftrag->rechnungs as $rechnung)
                {
                    $netto	 += $rechnung->NETTOBETRAG;
                    $brutto	 += $rechnung->BRUTTOBETRAG;
                }

                $dAuftrag = new AuftragAddons();
                $dAuftrag->auftrag_id = $request->getParam('auftrag_id');
                $dAuftrag->id = $auftrag->AUFTRAGID;
                $dAuftrag->fahrzeug_id	 = $auftrag->FAHRZEUGID;
                $dAuftrag->auftragnr = $auftrag->AUFTRAGSNR;
                $dAuftrag->date = $auftrag->ANNAHME;
                $dAuftrag->type = Auftrag::TYPE[$auftrag->TYP];

                $dAuftrag->anlieferung = $auftrag->ANLIEFERUNG;
                $dAuftrag->fertigstellung = $auftrag->FERTIGPLAN;
                $dAuftrag->save();

                $this->updatePositionsFromMACS($auftrag);
            }
        } else {
            return $response->withJson($errors, 422);
        }

        return $response->withJson('ok');
    }

    function step($request, $response, $arg)
    {
        $this->checkPermission($request, $response);

        /* --> Head */
        if (!$auftrag = Auftrag::find($arg['id'])) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('Page not found');
        }
        $this->setAuftragGeplant($auftrag);
        $this->updatePositionsFromMACS($auftrag);

        $auftrag->fahrzeug->auftrag_count = MACS\Auftrag::find($auftrag->id)->fahrzeug->auftrags()->count();
        $auftrag->fahrzeug->save();

        if(!$auftrag_m = MACS\Auftrag::find($auftrag->id)) {
            return '404 - Deleted from MACS';
        }

        $flatrates = [];
        if(isset($auftrag_m->fzFlatRate)) {
            $flatrates = $auftrag->getFlatrates($auftrag_m->fzFlatRate->FZFLATRATEID);
        }

        $fahrzeug_auftrags = $auftrag_m->fahrzeug->auftrags()->orderBy('ANLAGEDAT', 'DESC')->get();
        $fahrzeug_auftrags = MACS\Fahrzeug::where('FAHRGESTELLNUMMER', 'JMZBP6HEA01128118')->first()->auftrags()->orderBy('ANLAGEDAT', 'DESC')->get();

        $auftrags_daten = $auftrag_m->fahrzeug->auftrags()
            ->where('TYP', 2)
            ->whereBetween('ANLAGEDAT', [date("Y-m-d h:i:s", strtotime("-2 year")), date("Y-m-d h:i:s")])
            ->orderBy('ANLAGEDAT', 'DESC')
            ->get();

        $filemanager_counts = Filemanager::where('auftrag_id', $auftrag->id)->count();

        $garantie_kulanzantrag = GarantieKulanzantrag::orderBy('id', 'desc')->get();

        /* --> Steps */
        $step_complete = true;
        if(!in_array($arg['step'], array_keys((new StepController($this->container))->stepList()))) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('Page not found');
        } else {
            $step_complete = $this->checkStep($auftrag, $arg['step']);
        }

        $step_data = collect([
            'steps' => $auftrag->stepStatus,
        ]);

        $tasks = [];

        if($arg['step'] == 'kundenannahme') {
            $query_kundenhinweises = $auftrag->kundenhinweises()->orderBy('id', 'DESC')->get();

            $step_data = $step_data->merge([
                'step_subs' => $auftrag->stepStatus()->where('step', $arg['step'])->first()->children,
                'kundenhinweises' => [
                    'count' => $query_kundenhinweises->count(),
                    'completed_count' => $auftrag->kundenhinweises()->where('status', 'success')->count(),
                    'items' => $query_kundenhinweises
                ],
                'infos' => [
                    'filter' => $auftrag->infotags()->where('sonstige', false)->get(),
                    'items' => $auftrag->infotags()->where('sonstige', false)->where('set', true)->get(),
                    'sonstige' => $auftrag->infotags()->where('sonstige', true)->get(),
                ]
            ]);
        } else if($arg['step'] == 'fahrzeugannahme') {
            // task
            /*$tasks['rep_empfehlung'] = Tasks::select('checker_auftrag_tasks.*', 'td.message',  'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('checker_auftrag_tasks.auftragnr', $auftrag->auftragnr)
            ->where('checker_auftrag_tasks.task', 'rep_empfehlung')
            ->where('checker_auftrag_tasks.step_target', 'fahrzeugannahme')
            ->get();*/

            /*$tasks['rep_empfehlung'] = Queries::rep_empfehlung($auftrag->auftragnr);

            $tasks['status'] = Helpers::tasks_complete($tasks['rep_empfehlung']);/

            // task history
            foreach($tasks['rep_empfehlung'] as $hi) {
                $history = new History();
                $history->set($hi);
                $history->next();
                $history->reverse();
                $tasks['history']['rep_empfehlung'][] = ['kundenhinweise_id' => $hi->id, 'items' => $history->items];
            }*/

            // step
            $query_kundenhinweises = $auftrag->kundenhinweises()->orderBy('id', 'DESC')->get();

            $step_data = $step_data->merge([
                'kundenhinweises' => [
                    'count' => $query_kundenhinweises->count(),
                    'completed_count' => $auftrag->kundenhinweises()->where('status', 'success')->count(),
                    'items' => $query_kundenhinweises
                ],
                'step_subs' => $auftrag->stepStatus()->where('step', $arg['step'])->first()->children,
                'reparaturhinweise' => $auftrag->reparaturhinweise()->orderBy('id', 'DESC')->get()
            ]);

        } else if($arg['step'] == 'reparatur') {

            // task
            /*$tasks['rep_empfehlung'] = Tasks::select('checker_auftrag_tasks.*', 'td.message',  'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('checker_auftrag_tasks.auftragnr', $auftrag->auftragnr)
            ->where('checker_auftrag_tasks.task', 'diagnose')
            ->where('checker_auftrag_tasks.parent', 0)
            ->where('checker_auftrag_tasks.step', 'fahrzeugannahme')
            ->where('checker_auftrag_tasks.step_target', 'reparatur')
            ->get();

            $tasks['rep_anweisung'] = Tasks::select('checker_auftrag_tasks.*', 'td.message',  'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('checker_auftrag_tasks.auftragnr', $auftrag->auftragnr)
            ->where('checker_auftrag_tasks.task', 'rep_empfehlung')
            ->where('checker_auftrag_tasks.step', 'fahrzeugannahme')
            ->where('checker_auftrag_tasks.step_target', 'reparatur')
            ->where('td.message', 'Ja')
            ->get();*/

            /*$tasks['behebung_kundehunweise'] = Queries::behebung_kundehunweise($auftrag->auftragnr);

            $tasks['rep_anweisung'] = Queries::rep_anweisung($auftrag->auftragnr);

            $tasks['status'] = Helpers::tasks_complete($tasks['behebung_kundehunweise']);

            // count
            $get_tasks_count['behebung_kundehunweise']['success'] = count(Queries::behebung_kundehunweise($auftrag->auftragnr, 'success'));
            $get_tasks_count['rep_anweisung']['success'] = count(Queries::rep_anweisung($auftrag->auftragnr, 'success'));
            $count_tasks['success'] = $get_tasks_count['behebung_kundehunweise']['success'] + $get_tasks_count['rep_anweisung']['success'];

            $get_tasks_count['behebung_kundehunweise']['notcompleted'] = count(Queries::behebung_kundehunweise($auftrag->auftragnr, 'notcompleted'));
            $get_tasks_count['rep_anweisung']['notcompleted'] = count(Queries::rep_anweisung($auftrag->auftragnr, 'notcompleted'));
            $count_tasks['notcompleted'] = $get_tasks_count['behebung_kundehunweise']['notcompleted'] + $get_tasks_count['rep_anweisung']['notcompleted'];

            // task history
            foreach($tasks['behebung_kundehunweise'] as $hi) {
                $history = new History();
                $history->set($hi);
                $history->next();
                $history->reverse();
                $tasks['history']['behebung_kundehunweise'][] = ['kundenhinweise_id' => $hi->id, 'items' => $history->items];
            }

            foreach($tasks['rep_anweisung'] as $hi) {
                $history = new History();
                $history->set($hi);
                $history->next();
                $history->reverse();
                $tasks['history']['rep_anweisung'][] = ['kundenhinweise_id' => $hi->id, 'items' => $history->items];
            }*/

            // step
            $positions[] = [
                'auftragnr' => $auftrag->auftragnr,
                'items' => $auftrag->positions()->whereIn('positionstype', ['Arbeitslohn', 'Ersatzteil'])->where('original_deleted', false)->get()
            ];
            foreach($auftrag->addon as $ad) {
                $positions[] = [
                    'auftragnr' => $auftrag->auftragnr,
                    'items' => $ad->positions()->whereIn('positionstype', ['Arbeitslohn', 'Ersatzteil'])->where('original_deleted', false)->get()
                ];
            }

            $query_kundenhinweises = $auftrag->kundenhinweises()->orderBy('id', 'DESC')->get();

            $step_data = $step_data->merge([
                'step_subs' => $auftrag->stepStatus()->where('step', $arg['step'])->first()->children,
                'kundenhinweises' => [
                    'count' => $query_kundenhinweises->count(),
                    'completed_count' => $auftrag->kundenhinweises()->where('status', 'success')->count(),
                    'items' => $query_kundenhinweises
                ],
                'auftragspositionen' => $positions,
                'kundenbeanstandung' => $auftrag->kundenbeanstandung()->orderBy('id', 'DESC')->get(),
                'vorschlag' => $auftrag->reparaturhinweise()->orderBy('id', 'DESC')->get(),
                'dokumentation' => $auftrag->dokumentation()->first(),
                'technische' => $auftrag->kundenhinweises()->where('type', 'technische')->get()
            ]);
        } else if($arg['step'] == 'reparaturabnahme') {
            // task
            /*$tasks['losen'] = Tasks::select('checker_auftrag_tasks.*', 'td.message', 'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('checker_auftrag_tasks.auftragnr', $auftrag->auftragnr)
            ->where('checker_auftrag_tasks.task', 'lösen')
            ->where('checker_auftrag_tasks.parent', 0)
            ->where('checker_auftrag_tasks.step_target', 'reparaturabnahme')
            ->get();

            $tasks['rep_anweisung'] = Tasks::select('checker_auftrag_tasks.*', 'td.message', 'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('checker_auftrag_tasks.auftragnr', $auftrag->auftragnr)
            ->where('checker_auftrag_tasks.task', 'rep_anweisung')
            ->where('checker_auftrag_tasks.step', 'reparatur')
            ->where('checker_auftrag_tasks.step_target', 'reparaturabnahme')
            ->get();

            $tasks['rep_empfehlung_nein'] = Tasks::select('checker_auftrag_tasks.*', 'td.message', 'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('checker_auftrag_tasks.auftragnr', $auftrag->auftragnr)
            ->where('checker_auftrag_tasks.task', 'rep_empfehlung')
            ->where('checker_auftrag_tasks.step', 'fahrzeugannahme')
            ->where('checker_auftrag_tasks.step_target', 'reparaturabnahme')
            ->where('td.message', 'Nein')
            ->get();

            $tasks['rep_empfehlung_nachternierung'] = Tasks::select('checker_auftrag_tasks.*', 'td.message', 'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('checker_auftrag_tasks.auftragnr', $auftrag->auftragnr)
            ->where('checker_auftrag_tasks.task', 'rep_empfehlung')
            ->where('checker_auftrag_tasks.step', 'fahrzeugannahme')
            ->where('checker_auftrag_tasks.step_target', 'reparaturabnahme')
            ->where('td.message', 'Nachternierung')
            ->get();

            $tasks['rep_empfehlung'] = Queries::reparaturabnahme_rep_empfehlung($auftrag->auftragnr);

            $tasks['status'] = Helpers::tasks_complete($tasks['rep_empfehlung']);*/

            // step
            $query_kundenhinweises = $auftrag->kundenhinweises()->orderBy('id', 'DESC')->get();

            $step_data = $step_data->merge([
                'step_subs' => $auftrag->stepStatus()->where('step', $arg['step'])->first()->children,
                'abnahme' => $auftrag->positions()->whereIn('positionstype', ['Arbeitslohn', 'Ersatzteil'])->where('original_deleted', false)->get(),
                'probefahrt' => $auftrag->probefahrt()->first(),
                'statusmeldung_kundenhinweis' => [
                    'count' => $query_kundenhinweises->count(),
                    'items' => $query_kundenhinweises
                ],
                'dokumentation' => $auftrag->reparaturabnahmeDokumentation()->first(),
            ]);
        } else if($arg['step'] == 'rechnungsstellung') {
            // task
            /*$tasks['rep_empfehlung_nachternierung'] = Tasks::select('checker_auftrag_tasks.*', 'td.message', 'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('checker_auftrag_tasks.auftragnr', $auftrag->auftragnr)
            ->where('checker_auftrag_tasks.task', 'rep_empfehlung_nachternierung')
            ->where('checker_auftrag_tasks.step', 'reparaturabnahme')
            ->where('checker_auftrag_tasks.step_target', 'rechnungsstellung')
            ->get();*/

            // step
        }

        /* --> Template */
        $this->view->render($response, 'checker/step/' . $arg['step'] . '.twig', [
            'auftrag' => $auftrag,
            'auftrag_m' => $auftrag_m,
            'fahrzeug_auftrags' => $fahrzeug_auftrags,
            'flatrates' => $flatrates,
            'step_data' => $step_data,
            'step' => $arg['step'],
            'step_complete' => $step_complete,
            'filemanager_counts' => $filemanager_counts,
            'auftrags_daten' => $auftrags_daten,
            'tasks' => $tasks,
            'garantie_kulanzantrag' => $garantie_kulanzantrag
        ]);
    }

    private function setAuftragGeplant(&$auftrag)
    {
        $rp = MACS\Auftrag::find($auftrag->id)->reparatur_plan->first();
        if(isset($rp)) {
            $auftrag->geplant = $rp->DATUM;
            $auftrag->save();
        }
    }

    private function stepInit($auftrag)
    {
        $step = new StepController($this->container);
        $step->init($auftrag);

        $step_kundeannahme_infos = new Infos($this->container);
        $step_kundeannahme_infos->init($auftrag);
    }

    private function checkStep($auftrag, $step)
    {
        return StepController::checkStatus($auftrag, $step);
    }

    private function checkPermission($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('checker.show'))
        {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
    }

    private function updatePositionsFromMACS($auftrag)
    {
        $auftrag_m = MACS\Auftrag::find($auftrag->id);

        if(!isset($auftrag_m)) {
            $auftrag_m = MACS\Auftrag::find($auftrag->AUFTRAGID);
        }

        if(isset($auftrag_m)) {
            foreach ($auftrag_m->positions as $position) {
                AuftragPosition::updateOrCreate(
                    ['id' => $position->AUFTRAGSPOSITIONID],
                    [
                        'id'		 => $position->AUFTRAGSPOSITIONID,
                        'auftragnr'	 => $auftrag_m->AUFTRAGSNR,
                        'positionstype'  => $this->mb->convert($position->typ->BEZEICHNUNG),
                        'nummer'         => $this->mb->convert($position->NUMMER),
                        'bezeichnung'	 => $this->mb->convert($position->BEZEICHNUNG),
                        'typ'		 => isset($auftrag->typ) ? $this->mb->convert($auftrag->typ->BEZEICHNUNG) : '',
                        'menge'          => $position->MENGE,
                        'preis'          => $position->PREIS,
                        'betrag'	 => $position->VKBETRAGN,
                        'status_a'       => isset($auftrag->status) ? $this->mb->convert($auftrag->status->BEZEICHNUNG) : '',
                        'bemerkung'      => $position->POSBEMERKUNG,
                        'anteil'         => $position->SPLITTANTEILPROZ,
                    ]
                );

                $this->setDeletedPositions($auftrag, $auftrag_m->positions);
            }
        }
    }

    private function setDeletedPositions($auftrag, $macsPositions)
    {
        $macsPosIDs	 = $macsPositions->pluck('AUFTRAGSPOSITIONID')->toArray();
        $localPosIDs	 = $auftrag->positions->pluck('id')->toArray();

        $deletedIDs = [];

        foreach ($localPosIDs as $id) {
            if (!in_array($id, $macsPosIDs)) {
                $deletedIDs[] = $id;
            }
        }
        if (!empty($deletedIDs)) {
            AuftragPosition::whereIn('id', $deletedIDs)->update(['original_deleted' => 1]);
        }
    }

    private function createFahrzeugFromMACS($auftrag)
    {
        Fahrzeug::updateOrCreate(
            ['id' => $auftrag->FAHRZEUGID],
            [
                'id'		 => $auftrag->FAHRZEUGID,
                'vin'		 => $auftrag->fahrzeug->FAHRGESTELLNUMMER,
                'mark'		 => $this->mb->convert($auftrag->fahrzeug->hersteller->HERSTELLERNAME),
                'model'		 => $this->mb->convert($auftrag->fahrzeug->SERIE),
                'kmstand'	 => $auftrag->fahrzeug->fzh_actuell->KMSTAND,
                'year'		 => new \DateTime($auftrag->fahrzeug->ERSTZULASSUNG),
                'fabre'		 => $this->mb->convert($auftrag->fahrzeug->FARBEINNEN),
            ]
        );
    }

    private function createKundeFromMACS($auftrag)
    {

        $address = $this->mb->convert($auftrag->kunde->adresse->STRASSE) . ',<br/>' .
            $auftrag->kunde->adresse->LKZ . ', ' . $auftrag->kunde->adresse->PLZ . ' ' .
            $this->mb->convert($auftrag->kunde->adresse->ORT);

        Kunde::updateOrCreate(
            ['id' => $auftrag->KUNDEID],
            [
                'id'	 => $auftrag->KUNDEID,
                'kundennr'	 => $auftrag->kunde->KUNDENNR,
                'name'	 => $this->mb->convert($auftrag->kunde->adresse->NAME1),
                'kurzname'	 => $this->mb->convert($auftrag->kunde->adresse->KURZNAME),
                'anrede'	 => $this->mb->convert($auftrag->kunde->adresse->anrede->ANREDETEXT),
                'address'	 => $address
            ]
        );

        $this->createKundeKomm($auftrag);
        $this->createKundeKontaktTer($auftrag);
        $this->createDatenschutz($auftrag);
    }

    private function createKundeKontaktTer($auftrag)
    {
        foreach ($auftrag->kunde->kontaktTer as $kter)
        {
            KundeDSE::updateOrCreate(
                ['id' => $kter->KONTAKTERLAUBNISHISTOID],
                [
                    'id'		 => $kter->KONTAKTERLAUBNISHISTOID,
                    'kunde_id'	 => $auftrag->KUNDEID,
                    'date'		 => $kter->DATUM,
                    'type'		 => $this->mb->convert($kter->KONTAKTERLAUBNISTYP),
                    'val'		 => $kter->KONTAKTERLAUBNIS,
                ]
            );
        }
    }

    private function createDatenschutz($auftrag)
    {
        foreach ($auftrag->kunde->datenschutz as $d)
        {
            KundeDatenschutz::updateOrCreate(
                ['id' => $d->KUNDEID],
                [
                    'id'		 => $d->KUNDEID,
                    'brief'		 => $d->ISBRIEFKONTAKT,
                    'mail'		 => $d->ISEMAILKONTAKT,
                    'sms'		 => $d->ISSMSKONTAKT,
                    'telefon'        => $d->ISTELEFONKONTAKT,
                ]
            );
        }
    }

    private function createKundeKomm($auftrag)
    {
        foreach ($auftrag->kunde->adresse->kommunications as $komm)
        {
            KundeComm::updateOrCreate(
                ['id' => $komm->KOMMUNIKATIONID],
                [
                    'id'		 => $komm->KOMMUNIKATIONID,
                    'kunde_id'	 => $auftrag->KUNDEID,
                    'type_id'	 => $komm->KOMM_ARTID,
                    'type'		 => $this->mb->convert($komm->art->KOMM_ARTTEXT),
                    'val'		 => $komm->SUCHNR ?? $this->mb->convert($komm->KOMM_TEXT),
                ]
            );
        }
    }

    private function createKundenhinweise($auftrag)
    {
        $get = Libs\Helpers::getKundenhinweise_auftrag($this, $auftrag);

        foreach($get['kundenhinweise'] as $k) {
            $set = new AuftragKundenannahmeKundenhinweise();
            $set->auftragnr = $auftrag['AUFTRAGSNR'];
            $set->title = $k;
            $set->from_macs = true;
            $set->type = 'technische';
            $set->save();
        }
    }

    private function createFrzg($auftrag)
    {
        $set = new AuftragKundenannahmeFrzg();
        $set->auftragnr = $auftrag['AUFTRAGSNR'];
        $set->save();
    }

    private function createDokumentation($auftrag)
    {
        $set = new AuftragDiagnosereparaturDokumentation();
        $set->auftragnr = $auftrag['AUFTRAGSNR'];
        $set->save();
    }

    private function createProbefahrt($auftrag)
    {
        $set = new AuftragReparaturabnahmeProbefahrt();
        $set->auftragnr = $auftrag['AUFTRAGSNR'];
        $set->save();
    }

    private function createReparaturabnahmeDokumentation ($auftrag)
    {
        $set = new AuftragReparaturabnahmeDokumentation();
        $set->auftragnr = $auftrag['AUFTRAGSNR'];
        $set->save();
    }

    public function quests($request, $response)
    {
        $auftrag = Auftrag::where('auftragnr', $request->getParam('auftragnr'))->first();
        $kundenhinweises = $auftrag->kundenhinweises()->where('status', $request->getParam('type'))->orderBy('id', 'DESC')->get();

        if($request->getParam('type') == 'success') {
            return $this->view->fetch('checker/head/templates/aufgaben/success.twig', ['auftrag' => $auftrag, 'kundenhinweises' => $kundenhinweises]);
        } else {
            return $this->view->fetch('checker/head/templates/aufgaben/notcompleted.twig', ['auftrag' => $auftrag, 'kundenhinweises' => $kundenhinweises]);
        }
    }
}
