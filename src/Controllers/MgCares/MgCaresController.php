<?php
namespace App\Controllers\MgCares;

use App\Controllers\Controller;
use App\Models\MACS\Auftrag;
use App\Models\Junge\MgCare;

class MgCaresController extends Controller
{
    public function index($request, $response) 
    {
        if (!in_array('Service', $this->Auth->user()->groups->pluck('name')->toArray())) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $auftrags = Auftrag::join('FAHRZEUG as f', 'f.FAHRZEUGID', '=' ,'AUFTRAG.FAHRZEUGID')
            ->join('HERSTELLER as hs', 'hs.HERSTELLERID', '=', 'AUFTRAG.HERSTELLERID')
            ->where('AUFTRAG.STATUS', 1)
            ->whereRaw('UPPER("hs"."KURZNAME") LIKE \'%MG%\'')
            ->orderBy('AUFTRAG.ANNAHME', 'DESC')
            ->get();
        
        $cares = [];

        $this->view->render($response, 'mg_cares/index.twig', ['auftrags'=> $auftrags, 'cares' => $cares]);
    }
    
    public function search_js($request, $response)
    {
        $string = $request->getParam('string') ?: '';

        $results = Auftrag::select(
            'AUFTRAG.AUFTRAGID',
            'AUFTRAG.AUFTRAGSNR',
            'f.FAHRGESTELLNUMMER'
        )
        ->join('FAHRZEUG as f', 'f.FAHRZEUGID', '=', 'AUFTRAG.FAHRZEUGID')
        ->where('f.FAHRGESTELLNUMMER', 'like', '%' . $string . '%')
        ->limit(10)
        ->get();

        $collect = [];
        foreach($results as $r) {
            $collect[] = [
                'AUFTRAGID' => $r->AUFTRAGID,
                'AUFTRAGSNR' => $r->AUFTRAGSNR,
                'FAHRGESTELLNUMMER' => $r->FAHRGESTELLNUMMER,
            ];
        }

        return $response->withJson(collect($collect));
    }
    
    public function search($request, $response)
    {
        if (!in_array('Service', $this->Auth->user()->groups->pluck('name')->toArray())) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $picked = $request->getParam('vin');
        if(empty($request->getParam('vin'))) {
            $picked = $request->getParam('vin_title');
        }
            
        $auftrags = Auftrag::join('FAHRZEUG as f', 'f.FAHRZEUGID', '=' ,'AUFTRAG.FAHRZEUGID')
            ->join('HERSTELLER as hs', 'hs.HERSTELLERID', '=', 'AUFTRAG.HERSTELLERID')
            ->where('AUFTRAG.STATUS', 1)
            ->whereRaw('UPPER("hs"."KURZNAME") LIKE \'%MG%\'')
            ->where('f.FAHRGESTELLNUMMER', $picked)
            ->orderBy('AUFTRAG.ANNAHME', 'DESC')
            ->get();
        
        $cares = MgCare::where('VIN', $picked)->get();

        $this->view->render($response, 'mg_cares/index.twig', ['auftrags'=> $auftrags, 'cares' => $cares]);
    }
}
