<?php
namespace App\Controllers\Checker\Step\Reparaturabnahme;

use App\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use App\Models\MACS;
use App\Models\Checker\AuftragKundenannahmeKundenhinweise;

class Kundenhinweise extends Controller
{
    private function checkPermission($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('checker.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
    }

    public function erledigt($request, $response)
    {
        $set = AuftragKundenannahmeKundenhinweise::find($request->getParam('id'));
        $set->erledigt = $request->getParam('text');
        $set->save();

        return $response->withJson('ok');
    }
}
