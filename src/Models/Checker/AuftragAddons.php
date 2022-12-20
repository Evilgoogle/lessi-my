<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class AuftragAddons extends Model
{

    public $timestamps	 = false;
    public $incrementing	 = false;
    protected $primaryKey	 = 'id';
    protected $table	 = 'checker_auftrag_addons';
    protected $fillable	 = [
	'id',
	'auftragnr',
	'date',
	'type',
	'geplant',
    ];
    const TYPE = [
	0	 => 'extern',
	1	 => 'intern',
	2	 => 'garantie'
    ];

    public function fahrzeug()
    {
	return $this->hasOne(Fahrzeug::class, 'id', 'fahrzeug_id');
    }

    public function MACSFahrzeug()
    {
	return $this->hasOne(\App\Models\MACS\Fahrzeug::class, 'FAHRZEUGID', 'fahrzeug_id');
    }

    public function kunde()
    {
	return $this->hasOne(Kunde::class, 'id', 'kunde_id');
    }

    public function MACSKunde()
    {
	return $this->hasOne(\App\Models\MACS\Kunde::class, 'KUNDEID', 'kunde_id');
    }

    public function positions()
    {
	return $this->hasMany(AuftragPosition::class, 'auftragnr', 'auftragnr');
    }
    
    public function betrag_sum()
    {
        $position_betraq_sum = 0;
        foreach($this->positions()->where('original_deleted', false)->get()->pluck('betrag') as $p) {
            $position_betraq_sum += (float)$p;
        }
        
        return $position_betraq_sum;
    }
    
    public function MACSreparaturArt()
    {
        $macs = \App\Models\MACS\Auftrag::where('AUFTRAGSNR', $this->auftragnr)->first();
        
        return $macs->reparaturArt->BEZEICHNUNG;
    }
    
    public function wspakets()
    {
        $auftrag_m = \App\Models\MACS\Auftrag::where('AUFTRAGSNR', $this->auftragnr)->first();
        
        $wspakets = collect();
        foreach($auftrag_m->positions as $p) {
            if($p->paketPosition != null) {
                $wspakets->push($p->paketPosition->paket->PAKETBEZ);
            }
        }
        $wspakets = $wspakets->unique();
    }
}
