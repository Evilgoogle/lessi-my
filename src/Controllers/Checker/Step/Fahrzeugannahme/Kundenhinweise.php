<?php
namespace App\Controllers\Checker\Step\Fahrzeugannahme;

use App\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use App\Models\MACS;
use App\Models\Checker\AuftragKundenannahmeKundenhinweise;
use App\Models\Checker\Tasks;
use App\Controllers\Checker\StepController;
use App\Models\Checker\TasksDataTechnischeHinweise;
use App\Controllers\Checker\Tasks\Technische;

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
        $template = $this->view->fetch('checker/part/fahrzeugannahme/kundenhinweise/item.twig', ['id' => $request->getParam('id'), 'method' => 'insert', 'step' => $request->getParam('step')]);

        return $response->withJson($template);
    }

    public function add($request, $response)
    {
        $set = new AuftragKundenannahmeKundenhinweise();
        $set->auftragnr = $request->getParam('auftragnr');
        $set->title = $request->getParam('title');
        $set->type = $request->getParam('type');
        $set->save();

        $head = $this->view->fetch('checker/part/fahrzeugannahme/kundenhinweise/template/blocks/head/edit.twig', ['item' => $set]);
        $body = $this->view->fetch('checker/part/fahrzeugannahme/kundenhinweise/template/'.$request->getParam('type').'.twig', ['item' => $set]);

        StepController::update_child($request->getParam('auftragnr'), $request->getParam('step'), 'kundenhinweise');

        return $response->withJson([
            'kundenhinweise' => $set,
            'head' => $head,
            'body' => $body,
            'count_success' => AuftragKundenannahmeKundenhinweise::where('auftragnr', $request->getParam('auftragnr'))->where('status', 'success')->count(),
            'count_notcompleted' => AuftragKundenannahmeKundenhinweise::where('auftragnr', $request->getParam('auftragnr'))->where('status', 'notcompleted')->count()
        ]);
    }

    public function edit($request, $response)
    {
        $set = AuftragKundenannahmeKundenhinweise::find($request->getParam('id'));
        $set->title = $request->getParam('title');
        $set->save();

        return $response->withJson('ok');
    }

    public function set($request, $response)
    {
        $item = AuftragKundenannahmeKundenhinweise::find($request->getParam('id'));

        $item->type = $request->getParam('type');
        $item->save();

        $template = $this->view->fetch('checker/part/fahrzeugannahme/kundenhinweise/template/'.$request->getParam('type').'.twig', ['item' => $item]);

        return $response->withJson($template);
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

    public function more($request, $response)
    {
        $get = Tasks::select('checker_auftrag_tasks.*', 'td.message',  'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('checker_auftrag_tasks.auftragnr', $request->getParam('auftragnr'))
            ->get();

        $success = []; $notcompleted = [];
        foreach($get as $item) {
            if($item->child() !== null) {
                $success[] = $item;
            } else {
                if($item->step_target == 'end') {
                    $notcompleted[] = $item;
                }
            }
        }

        if($request->getParam('type') == 'success') {
            $html = $this->view->fetch('checker/head/templates/hinweise.twig', ['items' => $success]);
        } else {
            $html = $this->view->fetch('checker/head/templates/hinweise.twig', ['items' => $notcompleted]);
        }

        return $response->withJson($html);
    }

    public function save($request, $response)
    {
        $result = null;
        if($request->getParam('insert_task_type') != 'empty') {
            $result = (new Technische($this->container))->add($request, $response);
        }

        if($request->getParam('hinweise_type') == 'technische') {
            $get = Tasks::where('auftragnr', $request->getParam('auftragnr'))->where('external_id', $request->getParam('kundenhinweise_id'))->first();

            $set = AuftragKundenannahmeKundenhinweise::find($request->getParam('kundenhinweise_id'));
            if(isset($get)) {

                if($get->task != 'diagnose') {
                    $set->status = 'success';
                    $set->save();
                } else {
                    $set->status = 'notcompleted';
                    $set->save();
                }

            } else {
                $set->status = 'notcompleted';
                $set->save();
            }
        } else {
            $set = AuftragKundenannahmeKundenhinweise::find($request->getParam('kundenhinweise_id'));
            $set->status = 'success';
            $set->save();
        }

        return $response->withJson([
            'status' => $set->status,
            'result' => $result,
            'count_success' => AuftragKundenannahmeKundenhinweise::where('auftragnr', $request->getParam('auftragnr'))->where('status', 'success')->count(),
            'count_notcompleted' => AuftragKundenannahmeKundenhinweise::where('auftragnr', $request->getParam('auftragnr'))->where('status', 'notcompleted')->count()
        ]);
    }
}
