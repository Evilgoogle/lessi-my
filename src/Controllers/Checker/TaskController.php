<?php
namespace App\Controllers\Checker;

/**
 * Description of TaskController
 *
 * @author Evil_Google
 */
class TaskController
{
    static function diagnose_reparatur($controller, $auftragnr, $step, $tasks)
    {
        $status = 'notcompleted';

        $tasks = json_decode($tasks);

        /*if($tasks->task_type == 'rep_empfehlung') {

            $task = \App\Models\Checker\Tasks::find($tasks->child_id);
            if(!isset($task)) {
                $task = new \App\Models\Checker\Tasks();
            }
            $task->parent = $tasks->parent;
            $task->auftragnr = $auftragnr;
            $task->external_id = $tasks->kundenhinweise_id;
            $task->task = $tasks->task_type;
            $task->task_fullname = json_encode([$step, 'task', $tasks->task_type]);
            $task->step = $step;
            $task->step_target = 'fahrzeugannahme';
            $task->save();

            $data = \App\Models\Checker\TasksDataTechnischeHinweise::where('task_id', $task->id)->first();
            if(!isset($data)) {
                $data = new \App\Models\Checker\TasksDataTechnischeHinweise();
            }
            $data->task_id = $task->id;
            $data->message = ($tasks->message != '') ? $tasks->message : null;
            $data->complete = 'success';
            $data->save();

            $status = 'success';

            return [
                'status' => $status,
                'task_id' => $task->id
            ];
        } else if($tasks->task_type == 'rep_anweisung') {

            $task = \App\Models\Checker\Tasks::find($tasks->child_id);
            if(!isset($task)) {
                $task = new \App\Models\Checker\Tasks();
            }
            $task->parent = $tasks->parent;
            $task->auftragnr = $auftragnr;
            $task->external_id = $tasks->kundenhinweise_id;
            $task->task = $tasks->task_type;
            $task->task_fullname = json_encode([$step, 'task', $tasks->task_type]);
            $task->step = $step;
            $task->step_target = 'reparaturabnahme';
            $task->save();

            $data = \App\Models\Checker\TasksDataTechnischeHinweise::where('task_id', $task->id)->first();
            if(!isset($data)) {
                $data = new \App\Models\Checker\TasksDataTechnischeHinweise();
            }
            $data->task_id = $task->id;
            $data->message = null;
            $data->complete = 'success';
            $data->save();

            $status = 'success';

            return [
                'status' => $status,
                'task_id' => $task->id
            ];
        }*/

        if($tasks->task_type == 'behebung_kundehunweise') {

            $task = \App\Models\Checker\Tasks::find($tasks->child_id);
            if(!isset($task)) {
                $task = new \App\Models\Checker\Tasks();
            }
            $task->parent = $tasks->parent;
            $task->auftragnr = $auftragnr;
            $task->external_id = $tasks->kundenhinweise_id;
            $task->task = $tasks->task_type;
            $task->task_fullname = json_encode([$step, 'task', $tasks->task_type]);
            $task->step = $step;
            $task->step_target = ($tasks->select == 'Rep-Erweiterung') ? 'fahrzeugannahme' : 'reparaturabnahme';
            $task->set_user = $controller->Auth->user()->name;
            $task->set_time = date('Y-m-d H:i:s');
            $task->save();

            $data = \App\Models\Checker\TasksDataTechnischeHinweise::where('task_id', $task->id)->first();
            if(!isset($data)) {
                $data = new \App\Models\Checker\TasksDataTechnischeHinweise();
            }
            $data->task_id = $task->id;
            $data->message = ($tasks->message != '') ? $tasks->message : null;
            $data->select = $tasks->select;
            $data->complete = 'success';
            $data->save();

            $status = 'success';

            return [
                'status' => $status,
                'task_id' => $task->id
            ];
        } elseif($tasks->task_type == 'rep_anweisung') {
            $task = \App\Models\Checker\Tasks::find($tasks->child_id);
            if(!isset($task)) {
                $task = new \App\Models\Checker\Tasks();
            }
            $task->parent = $tasks->parent;
            $task->auftragnr = $auftragnr;
            $task->external_id = $tasks->kundenhinweise_id;
            $task->task = $tasks->task_type;
            $task->task_fullname = json_encode([$step, 'task', $tasks->task_type]);
            $task->step = $step;
            $task->step_target = 'end';
            $task->set_user = $controller->Auth->user()->name;
            $task->set_time = date('Y-m-d H:i:s');
            $task->save();

            $data = \App\Models\Checker\TasksDataTechnischeHinweise::where('task_id', $task->id)->first();
            if(!isset($data)) {
                $data = new \App\Models\Checker\TasksDataTechnischeHinweise();
            }
            $data->task_id = $task->id;
            $data->complete = 'success';
            $data->save();

            $status = 'success';

            return [
                'status' => $status,
                'task_id' => $task->id
            ];
        }
    }

    static function fahrzeugannahme($controller, $auftragnr, $step, $tasks)
    {
        $status = 'notcompleted';

        $tasks = json_decode($tasks);

        //if($tasks->task_type == 'rep_empfehlung')

        $target = 'end';
        if($tasks->task_type == 'diagnose') {
            $target = 'reparatur';
        }

        $task = \App\Models\Checker\Tasks::find($tasks->child_id);
        if(!isset($task)) {
            $task = new \App\Models\Checker\Tasks();
        }
        $task->parent = $tasks->parent;
        $task->auftragnr = $auftragnr;
        $task->external_id = $tasks->kundenhinweise_id;
        $task->task = $tasks->task_type;
        $task->task_fullname = json_encode(['fahrzeugannahme', 'kundenhinweise', 'technische', $tasks->task_type]);
        $task->step = 'fahrzeugannahme';
        $task->step_target = $target;
        $task->set_user = $controller->Auth->user()->name;
        $task->set_time = date('Y-m-d H:i:s');
        $task->save();

        $data = \App\Models\Checker\TasksDataTechnischeHinweise::where('task_id', $task->id)->first();
        if(!isset($data)) {
            $data = new \App\Models\Checker\TasksDataTechnischeHinweise();
        }
        $data->task_id = $task->id;
        $data->message = $tasks->message;
        $data->complete = 'success';
        $data->select = isset($tasks->select) ? $tasks->select : null;
        $data->save();

        $status = 'success';

        return [
            'status' => $status,
            'task_id' => $task->id
        ];

    }

    static function reparaturabnahme($controller, $auftragnr, $step, $tasks)
    {
        $status = 'notcompleted';

        $tasks = json_decode($tasks);

        /*if($tasks->task_type == 'losen') {
            $task = \App\Models\Checker\Tasks::find($tasks->child_id);
            if(!isset($task)) {
                $task = new \App\Models\Checker\Tasks();
            }
            $task->parent = $tasks->parent;
            $task->auftragnr = $auftragnr;
            $task->external_id = $tasks->kundenhinweise_id;
            $task->task = $tasks->task_type;
            $task->task_fullname = json_encode([$step, 'task', $tasks->task_type]);
            $task->step = $step;
            $task->step_target = 'rechnungsstellung';
            $task->save();

            $data = \App\Models\Checker\TasksDataTechnischeHinweise::where('task_id', $task->id)->first();
            if(!isset($data)) {
                $data = new \App\Models\Checker\TasksDataTechnischeHinweise();
            }
            $data->task_id = $task->id;
            $data->message = null;
            $data->complete = 'success';
            $data->save();

            $status = 'success';

            return [
                'status' => $status,
                'task_id' => $task->id
            ];
        } else if($tasks->task_type == 'rep_anweisung') {
            $task = \App\Models\Checker\Tasks::find($tasks->child_id);
            if(!isset($task)) {
                $task = new \App\Models\Checker\Tasks();
            }
            $task->parent = $tasks->parent;
            $task->auftragnr = $auftragnr;
            $task->external_id = $tasks->kundenhinweise_id;
            $task->task = $tasks->task_type;
            $task->task_fullname = json_encode([$step, 'task', $tasks->task_type]);
            $task->step = $step;
            $task->step_target = 'rechnungsstellung';
            $task->save();

            $data = \App\Models\Checker\TasksDataTechnischeHinweise::where('task_id', $task->id)->first();
            if(!isset($data)) {
                $data = new \App\Models\Checker\TasksDataTechnischeHinweise();
            }
            $data->task_id = $task->id;
            $data->message = null;
            $data->complete = 'success';
            $data->save();

            $status = 'success';

            return [
                'status' => $status,
                'task_id' => $task->id
            ];
        } else if($tasks->task_type == 'rep_empfehlung_nein') {
            $task = \App\Models\Checker\Tasks::find($tasks->child_id);
            if(!isset($task)) {
                $task = new \App\Models\Checker\Tasks();
            }
            $task->parent = $tasks->parent;
            $task->auftragnr = $auftragnr;
            $task->external_id = $tasks->kundenhinweise_id;
            $task->task = $tasks->task_type;
            $task->task_fullname = json_encode([$step, 'task', $tasks->task_type]);
            $task->step = $step;
            $task->step_target = 'rechnungsstellung';
            $task->save();

            $data = \App\Models\Checker\TasksDataTechnischeHinweise::where('task_id', $task->id)->first();
            if(!isset($data)) {
                $data = new \App\Models\Checker\TasksDataTechnischeHinweise();
            }
            $data->task_id = $task->id;
            $data->message = null;
            $data->complete = 'success';
            $data->save();

            $status = 'success';

            return [
                'status' => $status,
                'task_id' => $task->id
            ];
        } else if($tasks->task_type == 'rep_empfehlung_nachternierung') {
            $task = \App\Models\Checker\Tasks::find($tasks->child_id);
            if(!isset($task)) {
                $task = new \App\Models\Checker\Tasks();
            }
            $task->parent = $tasks->parent;
            $task->auftragnr = $auftragnr;
            $task->external_id = $tasks->kundenhinweise_id;
            $task->task = $tasks->task_type;
            $task->task_fullname = json_encode([$step, 'task', $tasks->task_type]);
            $task->step = $step;
            $task->step_target = 'rechnungsstellung';
            $task->save();

            $data = \App\Models\Checker\TasksDataTechnischeHinweise::where('task_id', $task->id)->first();
            if(!isset($data)) {
                $data = new \App\Models\Checker\TasksDataTechnischeHinweise();
            }
            $data->task_id = $task->id;
            $data->message = null;
            $data->complete = 'success';
            $data->save();

            $status = 'success';

            return [
                'status' => $status,
                'task_id' => $task->id
            ];
        }*/

        if($tasks->task_type == 'rep_empfehlung') {
            $task = \App\Models\Checker\Tasks::find($tasks->child_id);
            if(!isset($task)) {
                $task = new \App\Models\Checker\Tasks();
            }
            $task->parent = $tasks->parent;
            $task->auftragnr = $auftragnr;
            $task->external_id = $tasks->kundenhinweise_id;
            $task->task = $tasks->task_type;
            $task->task_fullname = json_encode([$step, 'task', $tasks->task_type]);
            $task->step = $step;
            $task->step_target = 'end';
            $task->set_user = $controller->Auth->user()->name;
            $task->set_time = date('Y-m-d H:i:s');
            $task->save();

            $data = \App\Models\Checker\TasksDataTechnischeHinweise::where('task_id', $task->id)->first();
            if(!isset($data)) {
                $data = new \App\Models\Checker\TasksDataTechnischeHinweise();
            }
            $data->task_id = $task->id;
            $data->select = $tasks->select;
            $data->complete = 'success';
            $data->save();

            $status = 'success';

            return [
                'status' => $status,
                'task_id' => $task->id
            ];
        }
    }

    static function rechnungsstellung($controller, $auftragnr, $step, $tasks)
    {
        $status = 'notcompleted';

        $tasks = json_decode($tasks);

        if($tasks->task_type == 'rep_empfehlung_nachternierung') {
            $task = \App\Models\Checker\Tasks::find($tasks->child_id);
            if(!isset($task)) {
                $task = new \App\Models\Checker\Tasks();
            }
            $task->parent = $tasks->parent;
            $task->auftragnr = $auftragnr;
            $task->external_id = $tasks->kundenhinweise_id;
            $task->task = $tasks->task_type;
            $task->task_fullname = json_encode([$step, 'task', $tasks->task_type]);
            $task->step = $step;
            $task->step_target = 'end';
            $task->set_user = $controller->Auth->user()->name;
            $task->set_time = date('Y-m-d H:i:s');
            $task->save();

            $data = \App\Models\Checker\TasksDataTechnischeHinweise::where('task_id', $task->id)->first();
            if(!isset($data)) {
                $data = new \App\Models\Checker\TasksDataTechnischeHinweise();
            }
            $data->task_id = $task->id;
            $data->message = null;
            $data->complete = 'success';
            $data->save();

            $status = 'success';

            return [
                'status' => $status,
                'task_id' => $task->id
            ];
        } else if($tasks->task_type == 'nachternierung') {

        }
    }
}
