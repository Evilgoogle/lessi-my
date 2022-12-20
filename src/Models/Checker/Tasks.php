<?php
namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;
use App\Controllers\Checker\Tasks\History;

class Tasks extends Model
{
    public $timestamps  = false;
    protected $table    = 'checker_auftrag_tasks';
    
    public function child() 
    {
        return Tasks::select('checker_auftrag_tasks.*', 'td.message', 'td.complete', 'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
            ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
            ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
            ->where('checker_auftrag_tasks.parent', $this->id)
            ->first();
    }
    
    public function history() 
    {
        $history = new History();
        $history->set($this);
        $history->next();
        $history->reverse();
        
        return $history->items;
    }
}
