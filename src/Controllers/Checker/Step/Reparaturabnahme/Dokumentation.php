<?php
namespace App\Controllers\Checker\Step\Reparaturabnahme;

use App\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use App\Models\MACS;
use App\Models\Checker\AuftragReparaturabnahmeDokumentation;

class Dokumentation extends Controller
{
    private function checkPermission($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('checker.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
    }

    public function update($request, $response)
    {
        $set = AuftragReparaturabnahmeDokumentation::find($request->getParam('id'));
        $set->text = $request->getParam('text');
        $set->save();

        return $response->withJson('ok');
    }
}
