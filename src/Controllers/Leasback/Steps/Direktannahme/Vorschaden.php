<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */
namespace App\Controllers\Leasback\Steps\Direktannahme;

use App\Controllers\Controller;
use App\Models\LeasBack\DirektannahmeTags;

/**
 * Description of Vorschaden
 *
 * @author joker
 */
class Vorschaden extends Controller 
{
    public function pick($request, $response) 
    {
        $set = DirektannahmeTags::
            where('leasback_id', $request->getParam('leasback_id'))
            ->where('type', 'vorschaden')
            ->where('tag', $request->getParam('tag'))
            ->first();
        
        if(!isset($set)) {
            $set = new DirektannahmeTags();
            $set->leasback_id = $request->getParam('leasback_id');
            $set->tag = $request->getParam('tag');
            $set->type = 'vorschaden';
            $set->save();
        }
               
        $template = $this->view->fetch('leasback/part/direktannahme/tag_view.twig', 
            [
                'tag' => $set,
                'leasback_id' => $request->getParam('leasback_id'), 
                'step' => 'direktannahme'
            ]
        );
        
        return $response->withJson($template);
    }
    
    public function form($request, $response)
    {
        $set = DirektannahmeTags::
            where('leasback_id', $request->getParam('leasback_id'))
            ->where('type', $request->getParam('type'))
            ->where('tag', $request->getParam('tag'))
            ->first();

        $set->thickness = $request->getParam('thickness');
        $set->image = !empty($request->getParam('file')) ? $request->getParam('file') : null;
        $set->save();
        
        return $response->withRedirect('/leasback'.'/'.$request->getParam('leasback_id').'/'.$request->getParam('step'));
    }
    
    public function keine($request, $response)
    {
        $set = DirektannahmeTags::
            where('leasback_id', $request->getParam('leasback_id'))
            ->where('type', 'vorschaden')
            ->where('tag', $request->getParam('tag'))
            ->first();

        $set->status = 'no';
        $set->save();
        
        return $response->withJson('ok');
    }
}
