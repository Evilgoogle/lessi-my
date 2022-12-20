<?php
namespace App\Controllers\Checker;

use App\Controllers\Controller;
use App\Models\Checker\StepStatus;
use App\Models\Checker\StepChildrenStatus;
use App\Models\Checker\AuftragKundenannahmeKundenhinweise;
use Mpdf\Mpdf;
use App\Controllers\Checker\TaskController;
use App\Models\Checker\Tasks;
use App\Controllers\Checker\Tasks\Queries;
use App\Controllers\Checker\Libs\Helpers;

class StepController extends Controller
{
    private $list = [
        'kundenannahme' => [
            'title' => 'Kundenannahme',
            'arr' => [
                'kundenhinweise' => 'Erfassung Kundenhinweise',
                'ersatzwagen' => 'Herausgabe Ersatzwagen',
                'infos' => 'Erfassung sonstige Infos',
                'frzg' => 'FRZG-Bereitstellung in DA',
            ]
        ],
        'fahrzeugannahme' => [
            'title' => 'Fahrzeugannahme',
            'arr' => [
                'vsc' => 'Durchführung VSC (Vorab-Sicherheits-Check) - DSR',
                'kundenhinweise' => 'Erfassung Kundenhinweise',
                //'reparaturhinweise' => 'Erfassung Reparaturhinweise für Mechaniker',
                //'annahmeprotokoll' => 'Erstellung Annahmeprotokoll Kundenfahrzeug',
            ]
        ],
        'reparatur' => [
            'title' => 'Reparatur',
            'arr' => [
                'auftragspositionen' => 'Abarbeitung Auftragspositionen',
                'kundenbeanstandung' => 'Rep-Empfehlung je Kundenbeanstandung (Freigabe SB notwendig)',
                //'vorschlag' => 'Vorschlag Rep-Erweiterung',
                //'dokumentation' => 'Dokumentation Diverse',
            ]
        ],
        'reparaturabnahme' => [
            'title' => 'Reparaturabnahme',
            'arr' => [
                'abnahme' => 'Technische FRZG-Abnahme',
                'probefahrt' => 'Probefahrt',
                'statusmeldung_kundenhinweis' => 'Statusmeldung Kundenhinweis',
                'dokumentation' => 'Dokumentation "offene Restarbeiten"',
            ]
        ],
        'rechnungsstellung' => [
            'title' => 'Rechnungsstellung',
            'arr' => [
                'auftragsvervollständigung' => 'Auftragsvervollständigung',
                'durchläuferteile' => 'Weiterbelastung Durchläuferteile und Fremdrechnung',
                'faktura' => 'Faktura',
                'verantwortlichkeit' => 'Festlegung Verantwortlichkeit für FRZG-Herausgabe und Rechnungserklärung (Empfang vs. SB vs. Schadenmanager)',
            ]
        ]
    ];

    public function init($auftrag)
    {
        $get = StepStatus::where('auftragnr', $auftrag->AUFTRAGSNR)->first();
        if(!isset($get)) {
            foreach($this->list as $step=>$arr) {
                $set = new StepStatus();
                $set->auftragnr = $auftrag->AUFTRAGSNR;
                $set->step = $step;
                $set->title = $arr['title'];
                $set->save();

                $this->children($set, $arr['arr']);
            }
        }
    }

    private function children($model, $arr)
    {
        foreach($arr as $child=>$val) {
            $set = new StepChildrenStatus();
            $set->step_id = $model->id;
            $set->step = $child;
            $set->title = $val;
            $set->save();
        }
    }

    public function stepList()
    {
        $arr = [];
        foreach($this->list as $k=>$v) {
            array_push($arr, $k);
        }

        return $arr;
    }

    static function checkStatus($auftrag, $step)
    {
        if($step == 'fahrzeugannahme') {
            $get = StepStatus::where('auftragnr', $auftrag->auftragnr)->where('step', 'kundenannahme')->where('status', 'success')->first();
        } else if($step == 'reparatur') {
            $get = StepStatus::where('auftragnr', $auftrag->auftragnr)->where('step', 'fahrzeugannahme')->where('status', 'success')->first();
        } else if($step == 'reparaturabnahme') {
            $get = StepStatus::where('auftragnr', $auftrag->auftragnr)->where('step', 'reparatur')->where('status', 'success')->first();
        } else if($step == 'rechnungsstellung') {
            $get = StepStatus::where('auftragnr', $auftrag->auftragnr)->where('step', 'reparaturabnahme')->where('status', 'success')->first();
        }

        if(isset($get)) {
            return true;
        }

        return false;
    }

    public function save_child($request, $response)
    {
        $step = StepStatus::where('auftragnr', $request->getParam('auftragnr'))->where('step', $request->getParam('step'))->first();

        $result = (object)[
            'kundenannahme' => null,
            'fahrzeugannahme' => null,
        ];

        $status = 'success';
        if(isset($step)) {
            if($step->step == 'kundenannahme' || $step->step == 'fahrzeugannahme') {
                if($step->step == 'kundenannahme') {

                    if($request->getParam('step_child') == 'frzg') {
                        $frzg = \App\Models\Checker\AuftragKundenannahmeFrzg::where('auftragnr', $request->getParam('auftragnr'))->first();
                        $frzg->status = $request->getParam('type_frzg');
                        $frzg->save();

                        $status = $frzg->status ? 'success' : 'notcompleted';
                    }
                    if($request->getParam('step_child') == 'ersatzwagen') {
                        $kunde = \App\Models\Checker\Auftrag::where('auftragnr', $request->getParam('auftragnr'))->first()->kunde;
                        $trips = \App\Models\Checker\Trip::where('kundenr', $kunde->kundennr)->get();

                        if($trips->count() > 0) {
                            $html = $this->container->get('view')->fetch('checker/part/kundenannahme/ersatzwagen/pdf.twig', [ 'trips' => $trips ]);

                            $name = 'file_'.$request->getParam('auftragnr').$kunde->kundennr.'.pdf';
                            $make = new Mpdf();
                            $make->WriteHTML($html);
                            $make->Output($name);

                            $new_name = DIRECTORY_SEPARATOR.'upload'.DIRECTORY_SEPARATOR.'checker/ersatzwagen'.DIRECTORY_SEPARATOR.$name;
                            rename($name, getcwd().$new_name);

                            $result->kundenannahme = ((!empty($_SERVER['HTTPS'])) ? 'https' : 'http') . '://' . $_SERVER['HTTP_HOST'].$new_name;
                        }
                    }
                }
                if($step->step == 'fahrzeugannahme') {
                    if($request->getParam('step_child') == 'vsc') {
                        /*$vin = \App\Models\Checker\Auftrag::where('auftragnr', $request->getParam('auftragnr'))->first()->fahrzeug->vin;

                        $result = Step\Fahrzeugannahme\Vsc::get($vin);

                        $status = 'notcompleted';
                        if(!empty($result->pdf_path)) {
                            $result->fahrzeugannahme = 'http://78.46.150.207/'.preg_replace('#\/root\/new_server\/#ui', '', $result->pdf_path);

                            $status = 'success';
                        }*/
                    }
                    if($request->getParam('step_child') == 'kundenhinweise') {
                        $get = AuftragKundenannahmeKundenhinweise::where('auftragnr', $request->getParam('auftragnr'))->where('status', 'notcompleted')->first();

                        $status = 'success';
                        if(isset($get)) {
                            $status = 'notcompleted';
                        }
                    }

                    if($request->getParam('step_child') == 'task') {
                        $get = TaskController::fahrzeugannahme($this, $request->getParam('auftragnr'), $request->getParam('step'), $request->getParam('tasks'));
                        $status = $get['status'];

                        $result->task_id = $get['task_id'];

                        if(json_decode($request->getParam('tasks'))->child_id == 'null') {
                            $task_type = json_decode($request->getParam('tasks'))->task_type;

                            $success = 'notcompleted';
                            if($task_type != 'diagnose') {
                                $success = 'success';
                            } else {
                                self::update_step($request->getParam('auftragnr'), 'reparatur');
                            }

                            $set = AuftragKundenannahmeKundenhinweise::find(json_decode($request->getParam('tasks'))->kundenhinweise_id);
                            $set->status = $success;
                            $set->save();
                        }
                    }
                }
            } else if($step->step == 'reparatur') {
                if($request->getParam('step_child') == 'auftragspositionen') {
                    $addons = \App\Models\Checker\Auftrag::where('auftragnr', $request->getParam('auftragnr'))->first()->addon()->pluck('auftragnr')->toArray();
                    array_push($addons, (int)$request->getParam('auftragnr'));

                    $get = \App\Models\Checker\AuftragPosition::
                    whereIn('positionstype', ['Arbeitslohn', 'Ersatzteil'])
                        ->where('original_deleted', false)
                        ->where('status', 'notcompleted')
                        ->whereIn('auftragnr', $addons)
                        ->get();

                    if($get->count() > 0) {
                        $status = 'notcompleted';
                    }
                }
                /*if($request->getParam('step_child') == 'vorschlag') {
                    $get = \App\Models\Checker\AuftragFahrzeugannahmeReparaturhinweise::where('status', 'notcompleted')->get();

                    if($get->count() > 0) {
                        $status = 'notcompleted';
                    }
                }*/

                if($request->getParam('step_child') == 'task') {
                    $get = TaskController::diagnose_reparatur($this, $request->getParam('auftragnr'), $request->getParam('step'), $request->getParam('tasks'));
                    $status = $get['status'];

                    $result->task_id = $get['task_id'];
                    $result->task_type = json_decode($request->getParam('tasks'))->task_type;

                    if(json_decode($request->getParam('tasks'))->child_id == 'null') {
                        $success = 'notcompleted';
                        if($result->task_type == 'behebung_kundehunweise') {
                            $select = json_decode($request->getParam('tasks'))->select;
                            self::update_step($request->getParam('auftragnr'), ($select == 'Rep-Erweiterung') ? 'fahrzeugannahme' : 'reparaturabnahme');

                            if($select == 'Behoben') {
                                $success = 'success';
                            }
                        } elseif($result->task_type == 'rep_anweisung') {
                            $success = 'success';
                        }

                        $set = AuftragKundenannahmeKundenhinweise::find(json_decode($request->getParam('tasks'))->kundenhinweise_id);
                        $set->status = $success;
                        $set->save();
                    }
                }
            } else if($step->step == 'reparaturabnahme') {
                if($request->getParam('step_child') == 'abnahme') {
                    $get = \App\Models\Checker\AuftragPosition::
                    whereIn('positionstype', ['Arbeitslohn', 'Ersatzteil'])
                        ->where('original_deleted', false)
                        ->where('status_abnahme', 'notcompleted')
                        ->where('auftragnr', $request->getParam('auftragnr'))
                        ->get();

                    if($get->count() > 0) {
                        $status = 'notcompleted';
                    }
                }
                if($request->getParam('step_child') == 'probefahrt') {
                    $get = \App\Models\Checker\AuftragReparaturabnahmeProbefahrt::
                    where('auftragnr', $request->getParam('auftragnr'))
                        ->where('status', 'notcompleted')
                        ->first();
                    if(isset($get)) {
                        $status = 'notcompleted';
                    }
                }

                if($request->getParam('step_child') == 'task') {
                    $get = TaskController::reparaturabnahme($this, $request->getParam('auftragnr'), $request->getParam('step'), $request->getParam('tasks'));
                    $status = $get['status'];

                    $result->task_id = $get['task_id'];
                }
            } else if($step->step == 'rechnungsstellung') {

                if($request->getParam('step_child') == 'task') {
                    $get = TaskController::rechnungsstellung($this, $request->getParam('auftragnr'), $request->getParam('step'), $request->getParam('tasks'));
                    $status = $get['status'];

                    $result->task_id = $get['task_id'];
                }
            }

            if($request->getParam('step_child') == 'task') {
                $child = (object)[];
                $child->status = $status;
            } else {
                $child = StepChildrenStatus::where('step_id', $step->id)->where('step', $request->getParam('step_child'))->first();
                $child->status = $status;
                $child->save();
            }

            $step_status = $this->updateStepStatus($step, $request->getParam('auftragnr'), $step->step);

            return $response->withJson([
                'child' => $child,
                'step_status' => $step_status,
                'result' => $result,
                'count_success' => AuftragKundenannahmeKundenhinweise::where('auftragnr', $request->getParam('auftragnr'))->where('status', 'success')->count(),
                'count_notcompleted' => AuftragKundenannahmeKundenhinweise::where('auftragnr', $request->getParam('auftragnr'))->where('status', 'notcompleted')->count()
            ]);
        }
    }

    private function updateStepStatus($step, $auftragnr, $step_title)
    {
        $all_childs_status = StepChildrenStatus::where('step_id', $step->id)->get()->pluck('status')->toArray();

        if(!in_array('notcompleted', $all_childs_status)) {
            if($step_title == 'fahrzeugannahme') {

                $tasks = Queries::rep_empfehlung($auftragnr);
                if(Helpers::tasks_complete($tasks)) {
                    $step->status = 'success';
                    $step->success_time = date('Y-m-d H:i:s');
                    $step->save();

                    $step_r = StepStatus::where('auftragnr', $auftragnr)->where('step', 'reparatur')->first();
                    $step_r->status = 'success';
                    $step_r->success_time = date('Y-m-d H:i:s');
                    $step_r->save();
                }

                /*$child = false;
                $get = AuftragKundenannahmeKundenhinweise::where('auftragnr', $auftragnr)->where('status', 'notcompleted')->first();
                if(isset($get)) {
                    $child = false;
                }

                if($task_1 && $child) {
                    $step->status = 'success';
                    $step->success_time = date('Y-m-d H:i:s');
                    $step->save();
                }*/

                /*// Для Servicebeaurator Monteur спецом шаг не закрывается когда создана задача
                $get = Queries::behebung_kundehunweise($auftragnr);
                if(!Helpers::tasks_complete($get)) {
                    $step->status = 'notcompleted';
                    $step->success_time = null;
                    $step->save();
                }*/
            } else if($step_title == 'reparatur') {

                $task_1 = false;
                $tasks_1 = Queries::behebung_kundehunweise($auftragnr);
                if(Helpers::tasks_complete($tasks_1)) {
                    $task_1 = true;
                }

                $task_2 = true;
                $get = AuftragKundenannahmeKundenhinweise::where('auftragnr', $auftragnr)->where('status', 'notcompleted')->first();
                if(isset($get)) {
                    $task_2 = false;
                }
                /*$tasks_2 = Queries::rep_anweisung($auftragnr);
                if(Helpers::tasks_complete($tasks_2)) {
                    $task_2 = true;
                }*/


                if($task_1 && $task_2) {
                    $step->status = 'success';
                    $step->success_time = date('Y-m-d H:i:s');
                    $step->save();
                }
            } else {
                $step->status = 'success';
                $step->success_time = date('Y-m-d H:i:s');
                $step->save();
            }
        } else {
            $step->status = 'notcompleted';
            $step->success_time = null;
            $step->save();
        }

        return $step;
    }

    static function update_step($auftragnr, $step)
    {
        $set = StepStatus::where('auftragnr', $auftragnr)->where('step', $step)->first();
        $set->status = 'notcompleted';
        $set->success_time = null;
        $set->save();

        return $set;
    }

    static function update_child($auftragnr, $step, $child_name)
    {
        $step = StepStatus::where('auftragnr', $auftragnr)->where('step', $step)->first();

        // Add new kundenhinweise
        if($step->step == 'kundenannahme' || $step->step == 'fahrzeugannahme' || $step->step == 'reparatur') {
            $child = StepChildrenStatus::where('step_id', $step->id)->where('step', $child_name)->first();
            $child->status = 'notcompleted';
            $child->save();

            $step->status = 'notcompleted';
            $step->success_time = null;
            $step->save();

            /*// Если в шаге Reparatur создалась задача, то fahrzeugannahme его должен классифицировать поэтому шаг fahrzeugannahme открывается
            if($step->step == 'reparatur') {
                $step = StepStatus::where('auftragnr', $auftragnr)->where('step', 'fahrzeugannahme')->first();

                $child = StepChildrenStatus::where('step_id', $step->id)->where('step', $child_name)->first();
                $child->status = 'notcompleted';
                $child->save();

                $step->status = 'notcompleted';
                $step->success_time = null;
                $step->save();
            }*/
        }
    }
}