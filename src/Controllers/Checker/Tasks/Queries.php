<?php
namespace App\Controllers\Checker\Tasks;

use App\Models\Checker\Tasks;

/**
 * Description of Queries
 *
 * @author Evil_Google
 */
class Queries 
{
    static function getter($get, $type) {
        $hinweise = [
            'success' => [],
            'notcompleted' => []
        ];
        foreach($get as $item) {
            if($item->child() !== null) {
                $hinweise['success'][] = $item;
            } else {

                if($item->step_target != 'end') {
                    $hinweise['notcompleted'][] = $item;
                }
            }
        }

        if($type == 'all') {
            return $get;
        } elseif($type == 'success') {
            return collect($hinweise['success']);
        } elseif($type == 'notcompleted') {
            return collect($hinweise['notcompleted']);
        }
    }
    
    static function rep_empfehlung($auftragnr, $id = false, $type = 'all')
    {
        $query = Tasks::select('checker_auftrag_tasks.*', 'td.message',  'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('checker_auftrag_tasks.auftragnr', $auftragnr)
            ->where('checker_auftrag_tasks.task', 'behebung_kundehunweise')
            ->where('checker_auftrag_tasks.step_target', 'fahrzeugannahme');
        
        if($id) {
            $query->where('external_id', $id);
        }
        $get = $query->get();
        
        return self::getter($get, $type);
    }
    
    static function behebung_kundehunweise($auftragnr, $id = false, $type = 'all')
    {
        $query = Tasks::select('checker_auftrag_tasks.*', 'td.message',  'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('checker_auftrag_tasks.auftragnr', $auftragnr)
            ->where('checker_auftrag_tasks.task', 'diagnose')
            ->where('checker_auftrag_tasks.step', 'fahrzeugannahme')
            ->where('checker_auftrag_tasks.step_target', 'reparatur');
        
        if($id) {
            $query->where('external_id', $id);
        }
        $get = $query->get();

        return self::getter($get, $type);
    }
    
    static function rep_anweisung($auftragnr, $id = false, $type = 'all')
    {
        $query = Tasks::select('checker_auftrag_tasks.*', 'td.message',  'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('checker_auftrag_tasks.auftragnr', $auftragnr)
            ->where('checker_auftrag_tasks.task', 'rep_empfehlung')
            ->where('checker_auftrag_tasks.step', 'fahrzeugannahme')
            ->where('checker_auftrag_tasks.step_target', 'reparatur')
            ->where('td.select', 'durch Auftragspositionen gelöst');
        
        if($id) {
            $query->where('external_id', $id);
        }
        $get = $query->get();
        
        return self::getter($get, $type);
    }
    
    static function reparaturabnahme_rep_empfehlung($auftragnr, $id = false, $type = 'all')
    {
        $query = Tasks::select('checker_auftrag_tasks.*', 'td.message', 'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('checker_auftrag_tasks.auftragnr', $auftragnr)
            ->where('checker_auftrag_tasks.task', 'behebung_kundehunweise')
            ->where('checker_auftrag_tasks.step', 'reparatur')
            ->where('checker_auftrag_tasks.step_target', 'reparaturabnahme');
        
        if($id) {
            $query->where('external_id', $id);
        }
        $get = $query->get();
        
        return self::getter($get, $type);
    }
}
