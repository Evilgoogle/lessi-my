<?php
namespace App\Controllers\Checker\Step\Diagnosereparatur;

use App\Controllers\Controller;
use App\Models\MACS;
use App\Models\Checker\AuftragDiagnosereparaturDokumentation;

class Dokumentation extends Controller
{    
    public function update($request, $response) 
    {
        $set = AuftragDiagnosereparaturDokumentation::find($request->getParam('id'));
        $set->text = $request->getParam('text');
        $set->save();
        
        return $response->withJson('ok');
    }
}