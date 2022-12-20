<?php
namespace App\Controllers\Checker\Step\Diagnosereparatur;

use App\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use App\Models\MACS;
use App\Models\Checker\AuftragPosition;

class Auftragspositionen extends Controller
{
    private function checkPermission($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('checker.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
    }

    public function status($request, $response)
    {
        $set = AuftragPosition::find($request->getParam('id'));

        if($request->getParam('type') == 'auftragspositionen') {
            $set->status = $set->status == 'success' ? 'notcompleted' : 'success';
        } elseif($request->getParam('type') == 'abnahme') {
            $set->status_abnahme = $set->status_abnahme == 'success' ? 'notcompleted' : 'success';
        }
        $set->save();

        return $response->withJson($set);
    }
}
