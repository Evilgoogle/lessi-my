<?php
namespace App\Controllers\Checker\Step\Kundenannahme;

use App\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use App\Models\MACS;
use App\Models\Checker\AuftragKundenannahmeKundenhinweise;
use App\Controllers\Checker\StepController;
use App\Models\Checker\Tasks;
use App\Models\Checker\TasksDataTechnischeHinweise;

class Kundenhinweise extends Controller
{
    private function checkPermission($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('checker.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
    }

    public function modal($request, $response)
    {
        $in_modal = $request->getParam('in_modal');

        $template = $this->view->fetch('checker/part/kundenannahme/kundenhinweise/item.twig', ['id' => $request->getParam('id'), 'method' => 'insert', 'step' => $request->getParam('step'), 'in_modal' => $in_modal]);

        return $response->withJson($template);
    }

    public function add($request, $response)
    {
        $set = new AuftragKundenannahmeKundenhinweise();
        $set->auftragnr = $request->getParam('auftragnr');
        $set->title = $request->getParam('title');
        $set->type = 'technische'; // временная тестовая
        $set->save();

        $in_modal = $request->getParam('in_modal');

        $head = $this->view->fetch('checker/part/kundenannahme/kundenhinweise/template/blocks/head/edit.twig', ['item' => $set, 'in_modal' => $in_modal]);
        $body = $this->view->fetch('checker/part/kundenannahme/kundenhinweise/template/item.twig', ['item' => $set, 'in_modal' => $in_modal]);

        StepController::update_child($request->getParam('auftragnr'), $in_modal == false ? $request->getParam('step') : 'fahrzeugannahme', 'kundenhinweise');

        return $response->withJson([
            'kundenhinweise' => $set,
            'head' => $head,
            'body' => $body,
            'count_success' => AuftragKundenannahmeKundenhinweise::where('auftragnr', $request->getParam('auftragnr'))->where('status', 'success')->count(),
            'count_notcompleted' => AuftragKundenannahmeKundenhinweise::where('auftragnr', $request->getParam('auftragnr'))->where('status', 'notcompleted')->count()
        ]);
    }

    public function remove($request, $response)
    {
        AuftragKundenannahmeKundenhinweise::where('id', $request->getParam('id'))->where('from_macs', false)->delete();

        foreach(Tasks::where('external_id', $request->getParam('id'))->get() as $del) {
            TasksDataTechnischeHinweise::where('task_id', $del->id)->delete();

            $del->delete();
        }

        return $response->withJson([
            'count_success' => AuftragKundenannahmeKundenhinweise::where('auftragnr', $request->getParam('auftragnr'))->where('status', 'success')->count(),
            'count_notcompleted' => AuftragKundenannahmeKundenhinweise::where('auftragnr', $request->getParam('auftragnr'))->where('status', 'notcompleted')->count()
        ]);
    }
}
