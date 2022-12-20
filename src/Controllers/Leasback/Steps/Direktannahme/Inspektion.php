<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Controllers\Leasback\Steps\Direktannahme;

use App\Controllers\Controller;
use App\Models\LeasBack\DirektannahmeInspektion;

/**
 * Description of Vorschaden
 *
 * @author joker
 */
class Inspektion extends Controller 
{
    public function pick($request, $response) 
    {
        $set = DirektannahmeInspektion::
            where('leasback_id', $request->getParam('leasback_id'))
            ->first();
        
        $set->type = $request->getParam('pick');
        $set->save();
        
        $template = $this->view->fetch('leasback/part/direktannahme/inspektion.twig', 
            [
                'inspektion' => $set,
            ]
        );
        
        return $response->withJson(['type' => $set->type, 'template' => $template]);
    }
    
    public function form($request, $response)
    {
        $set = DirektannahmeInspektion::find($request->getParam('id'));

        $set->kosten_text = $request->getParam('kosten_text');
        $set->save();
    }
}
