<?php
namespace App\Controllers\Checker\Tasks;
/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

use App\Models\Checker\Tasks;

/**
 * Description of History
 *
 * @author joker
 */
class History
{
    public $items = [];

    public function set($item) {
        if(isset($item)) {
            $this->items[] = $item;

            if($item->parent != 0) {
                $get = Tasks::select('checker_auftrag_tasks.*', 'td.message',  'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
                    ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
                    ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
                    ->where('checker_auftrag_tasks.id', $item->parent)
                    ->first();

                $this->set($get);
            }
        }
    }
    
    public function next()
    {
        $id = collect($this->items)->first()->id;
  
        $set = Tasks::select('checker_auftrag_tasks.*', 'td.message',  'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('parent', $id)
            ->first();
        if(isset($set)) {
            $set->last = 'ok';

            array_unshift($this->items, $set);
        }
    }
    
    public function reverse()
    {
        $this->items = array_reverse($this->items);
    }
}
