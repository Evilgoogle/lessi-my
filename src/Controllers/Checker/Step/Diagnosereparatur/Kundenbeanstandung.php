<?php
namespace App\Controllers\Checker\Step\Diagnosereparatur;

use App\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use App\Models\MACS;
use App\Models\Checker\AuftragDiagnosereparaturKundenbeanstandung;

class Kundenbeanstandung extends Controller
{
    private function checkPermission($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('checker.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
    }

    public function add($request, $response)
    {
        $set = new AuftragDiagnosereparaturKundenbeanstandung();
        $set->auftragnr = $request->getParam('auftragnr');
        $set->title = $request->getParam('title');
        $set->save();

        $template = $this->view->fetch('checker/part/reparatur/kundenbeanstandung/item.twig', ['item' => $set]);

        return $response->withJson($template);
    }

    public function edit($request, $response)
    {
        $get = AuftragDiagnosereparaturKundenbeanstandung::find($request->getParam('id'));

        return $response->withJson($get);
    }

    public function update($request, $response)
    {
        $set = AuftragDiagnosereparaturKundenbeanstandung::find($request->getParam('id'));
        $set->title = $request->getParam('title');
        $set->save();

        return $response->withJson($set);
    }

    public function remove($request, $response)
    {
        AuftragDiagnosereparaturKundenbeanstandung::where('id', $request->getParam('id'))->delete();

        return $response->withJson($request->getParam('id'));
    }
}
