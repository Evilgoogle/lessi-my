<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;
use App\Controllers\Checker\Tasks\Queries;

class AuftragKundenannahmeKundenhinweise extends Model
{
    public $timestamps	 = false;
    protected $primaryKey	 = 'id';
    protected $table	 = 'checker_auftrag_kundenannahme_kundenhinweises';
    protected $fillable	 = [
	'id',
	'title',
	'completed',
	'type'
    ];
    
    public function getKommentars()
    {
        return $this->hasMany(AuftragKundenhinweiseKommentars::class, 'kundenhinweise_id', 'id');
    }
    
    public function technische()
    {
        return $this->hasMany(AuftragKundenannahmeKundenhinweisesTechnische::class, 'kundenhinweise_id', 'id');
    }
    
    public function tasks($query = 'all')
    {      
        if($query == 'all') {
            
            return Tasks::select('checker_auftrag_tasks.*', 'td.message',  'td.select', 'k.id as kundenhinweise_id', 'k.title as kundenhinweise_title')
                ->join('checker_auftrag_tasks_data_technische-hinweise as td', 'td.task_id', '=', 'checker_auftrag_tasks.id')
                ->join('checker_auftrag_kundenannahme_kundenhinweises as k', 'k.id', '=', 'checker_auftrag_tasks.external_id')
                ->where('checker_auftrag_tasks.auftragnr', $this->auftragnr)
                ->where('external_id', $this->id)
                ->get();
        } elseif($query == 'rep_empfehlung') {
            
            return Queries::rep_empfehlung($this->auftragnr, $this->id);
        } elseif($query == 'behebung_kundehunweise') {
      
            return Queries::behebung_kundehunweise($this->auftragnr, $this->id);
        }
    }
}
