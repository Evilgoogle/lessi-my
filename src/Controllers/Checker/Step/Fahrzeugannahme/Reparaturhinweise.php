<?php
namespace App\Controllers\Checker\Step\Fahrzeugannahme;

use App\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use App\Models\MACS;
use App\Models\Checker\AuftragFahrzeugannahmeReparaturhinweise;

class Reparaturhinweise extends Controller
{
    private function checkPermission($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('checker.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
    }

    public function add($request, $response)
    {
        $set = new AuftragFahrzeugannahmeReparaturhinweise();
        $set->auftragnr = $request->getParam('auftragnr');
        $set->title = $request->getParam('title');
        $set->save();

        $template = $this->view->fetch('checker/part/fahrzeugannahme/reparaturhinweise/item.twig', ['item' => $set]);

        return $response->withJson($template);
    }

    public function edit($request, $response)
    {
        $get = AuftragFahrzeugannahmeReparaturhinweise::find($request->getParam('id'));

        return $response->withJson($get);
    }

    public function update($request, $response)
    {
        $set = AuftragFahrzeugannahmeReparaturhinweise::find($request->getParam('id'));
        $set->title = $request->getParam('title');
        $set->save();

        return $response->withJson($set);
    }

    public function remove($request, $response)
    {
        AuftragFahrzeugannahmeReparaturhinweise::where('id', $request->getParam('id'))->delete();

        return $response->withJson($request->getParam('id'));
    }

    public function status($request, $response)
    {
        $set = AuftragFahrzeugannahmeReparaturhinweise::find($request->getParam('id'));
        $set->status = $set->status == 'success' ? 'notcompleted' : 'success';
        $set->save();

        return $response->withJson($set);
    }
}
