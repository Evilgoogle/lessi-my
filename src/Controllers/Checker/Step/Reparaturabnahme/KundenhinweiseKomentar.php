<?php
namespace App\Controllers\Checker\Step\Reparaturabnahme;

use App\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use App\Models\MACS;
use App\Models\Checker\AuftragKundenhinweiseKommentars;

class KundenhinweiseKomentar extends Controller
{
    private function checkPermission($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('checker.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
    }

    public function add($request, $response)
    {
        $set = new AuftragKundenhinweiseKommentars();
        $set->kundenhinweise_id = $request->getParam('kundenhinweise_id');
        $set->message = $request->getParam('message');
        $set->save();

        return $response->withJson($this->view->fetch('checker/part/reparaturabnahme/statusmeldung_kundenhinweis/komment.twig', ['item' => $set]));
    }

    public function edit($request, $response)
    {
        $set = AuftragKundenhinweiseKommentars::find($request->getParam('id'));
        $set->message = $request->getParam('message');
        $set->save();

        return $response->withJson($set);
    }
}
