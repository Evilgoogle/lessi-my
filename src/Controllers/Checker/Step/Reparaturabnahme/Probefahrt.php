<?php
namespace App\Controllers\Checker\Step\Reparaturabnahme;

use App\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use App\Models\MACS;
use App\Models\Checker\AuftragReparaturabnahmeProbefahrt;

class Probefahrt extends Controller
{
    private function checkPermission($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('checker.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
    }

    public function status($request, $response)
    {
        $set = AuftragReparaturabnahmeProbefahrt::where('auftragnr', $request->getParam('auftragnr'))->first();
        $set->status = 'success';
        $set->user = 'test';
        $set->date = date('Y-m-d h:i:s');
        $set->save();

        return $response->withJson($set);
    }
}
