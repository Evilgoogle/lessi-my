<?php
namespace App\Controllers\Checker\Tasks;

use App\Controllers\Controller;
use App\Models\Checker\Tasks;
use App\Models\Checker\TasksDataTechnischeHinweise;
use \App\Controllers\Checker\StepController;

/**
 * Description of Technische
 *
 * @author EvilGoogle
 */
class Technische extends Controller
{
    public function index($request, $response)
    {
        $item = Tasks::select('checker_auftrag_tasks.*', 'td.message', 'td.select')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->where('auftragnr', $request->getParam('auftragnr'))
            ->where('external_id', $request->getParam('kundenhinweise_id'))
            ->where('parent', 0)
            ->first();

        if(isset($item)) {
            return $this->view->fetch('checker/tasks/technische/item.twig', ['item' => $item, 'auftragnr' => $request->getParam('auftragnr')]);
        } else {
            return $this->view->fetch('checker/tasks/technische/select.twig', ['kundenhinweise_id' => $request->getParam('kundenhinweise_id'), 'auftragnr' => $request->getParam('auftragnr')]);
        }
    }
    
    public function forms($request, $response)
    {   
        return $this->view->fetch('checker/tasks/technische/templates/'.$request->getParam('pick').'.twig', [
            'kundenhinweise_id' => $request->getParam('kundenhinweise_id'), 
            'auftragnr' => $request->getParam('auftragnr'),
            'place' => $request->getParam('place')
        ]);
    }
    
    public function add($request, $response) 
    {
        $target = 'end';
        if($request->getParam('insert_task_type')['task_type'] == 'diagnose') {
            $target = 'reparatur';
        }
        
        $task = new Tasks();
        $task->auftragnr = $request->getParam('auftragnr');
        $task->external_id = $request->getParam('kundenhinweise_id');
        $task->task = $request->getParam('insert_task_type')['task_type'];
        $task->task_fullname = json_encode(['fahrzeugannahme', 'kundenhinweise', 'technische', $request->getParam('insert_task_type')['task_type']]);
        $task->step = 'fahrzeugannahme';
        $task->step_target = $target;
        $task->set_user = $this->Auth->user()->name;
        $task->set_time = date('Y-m-d H:i:s');
        $task->save();
        
        $data = new TasksDataTechnischeHinweise();
        $data->task_id = $task->id;
        $data->message = $request->getParam('insert_task_type')['message'];
        $data->select = $request->getParam('insert_task_type')['select'] != '' ? $request->getParam('insert_task_type')['select'] : null;
        $data->complete = 'success';
        $data->save();
        
        $status = 'success';
        if($request->getParam('insert_task_type')['task_type'] == 'diagnose') {
            $status = StepController::update_step($request->getParam('auftragnr'), $target);
        }
        
        return [
            'status' => $status,
            'item' => $task,
            'template' => $this->view->fetch('checker/tasks/technische/item.twig', ['item' => $task, 'data' => $data, 'auftragnr' => $request->getParam('auftragnr')])
        ];
    }
}
