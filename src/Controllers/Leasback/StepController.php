<?php
namespace App\Controllers\Leasback;

use App\Controllers\Controller;
use App\Models\LeasBack\StepStatus;
use App\Models\LeasBack\StepStatusChild;
use App\Controllers\Leasback\Status\Einladung;

class StepController extends Controller
{
    private $list = [
        'einladung' => [
            'title' => 'Einladung',
            'arr' => [
                'mail' => 'Mail'
            ]
        ],
        'direktannahme' => [
            'title' => 'Direktannahme',
            'arr' => [
                'schaden' => 'Schäden',
                'vorschaden' => 'Vorschäden'
            ]
        ],
        'expert' => [
            'title' => 'Expert',
            'arr' => [
             
            ]
        ],
        'aktenvorbereitung' => [
            'title' => 'Aktenvorbereitung',
            'arr' => [
             
            ]
        ],
        'rucknahme' => [
            'title' => 'Rücknahme',
            'arr' => [
             
            ]
        ],
    ];
    
    static $access = [
        'einladung' => [
            'title' => 'Einladung',
            'access' => 'backoffice',
        ],
        'direktannahme' => [
            'title' => 'Direktannahme',
            'access' => 'mechaniker',
        ],
        'expert' => [
            'title' => 'Expert',
            'access' => 'expert',
        ],
        'aktenvorbereitung' => [
            'title' => 'Aktenvorbereitung',
            'access' => 'backoffice',
        ],
        'rucknahme' => [
            'title' => 'Rücknahme',
            'access' => 'backoffice',
        ],
    ];
    
    public function init($leasback)
    {
        $get = StepStatus::where('leasback_id', $leasback->id)->first();
        if(!isset($get)) {
            foreach($this->list as $step=>$arr) {
                $set = new StepStatus();
                $set->leasback_id = $leasback->id;
                $set->step = $step;
                $set->title = $arr['title'];
                $set->save();
                
                foreach($this->list[$step]['arr'] as $child=>$title) {
                    $child = new StepStatusChild();
                    $child->step_id = $set->id;
                    $child->step = $child;
                    $child->title = $title;
                    $child->save();
                }
            }
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
    
    static function check_access($user, $step) 
    {
        $result = false;
        foreach(self::$access as $key=>$item) {
            if($key == $step) {
                foreach($user->priviliges() as $p) {
                    if($p->code == 'leasback.'.$item['access']) {
                        $result = true;
                    }
                }
            }
        }
        
        return $result;
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
    
    public function save($request, $response) 
    {
        $leasback_id = $request->getParam('leasback_id');
        $type = $request->getParam('type');

        if($request->getParam('step') == 'einladung') {
            $set = new Einladung($leasback_id, $type);
            $set->start();
        } elseif($request->getParam('step') == 'direktannahme') {
            
        }

        return $response->withJson('ok');
    }
}