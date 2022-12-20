<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Capsule\Manager as DB;

class Auftrag extends Model
{

    public $timestamps	 = false;
    public $incrementing	 = false;
    protected $primaryKey	 = 'id';
    protected $table	 = 'checker_auftrag';
    protected $fillable	 = [
	'id',
	'auftragnr',
	'fahrzeug_id',
	'kunde_id',
	'date',
	'type',
	'geplant',
    ];
    const TYPE = [
	0	 => 'extern',
	1	 => 'intern',
	2	 => 'garantie'
    ];

    public function stepStatus()
    {
	return $this->hasMany(StepStatus::class, 'auftragnr', 'auftragnr');
    }

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
    
    public function kundenhinweises()
    {
	return $this->hasMany(AuftragKundenannahmeKundenhinweise::class, 'auftragnr', 'auftragnr');
    }
    
    public function reparaturhinweise()
    {
        return $this->hasMany(AuftragFahrzeugannahmeReparaturhinweise::class, 'auftragnr', 'auftragnr');
    }

    public function infotags()
    {
	return $this->hasMany(AuftragKundenannahmeInfoTag::class, 'auftragnr', 'auftragnr');
    }
    
    public function kundenbeanstandung() 
    {
        return $this->hasMany(AuftragDiagnosereparaturKundenbeanstandung::class, 'auftragnr', 'auftragnr');
    }

    public function dokumentation() 
    {
        return $this->hasMany(AuftragDiagnosereparaturDokumentation::class, 'auftragnr', 'auftragnr');
    }
    
    public function probefahrt()
    {
        return $this->hasMany(AuftragReparaturabnahmeProbefahrt::class, 'auftragnr', 'auftragnr');
    }
    
    public function reparaturabnahmeDokumentation()
    {
        return $this->hasMany(AuftragReparaturabnahmeDokumentation::class, 'auftragnr', 'auftragnr');
    }

    public function getFlatrates($FZFlATRATEID)
    {
        return collect(DB::connection('macs')->select('
            Select
                FL.FzFlatRateLeistungText,
                FRPLAN.NUMMER,
                FRPLAN.BEZEICHNUNG,
                CAST( CASE 
                    WHEN FzFlatRateStatusID = 1 THEN \'Kalkuliert\' 
                    WHEN FzFlatRateStatusID = 2 THEN \'laufender Vertrag\' 
                    WHEN FzFlatRateStatusID = 3 THEN \'Erledigt\' 
                ELSE NULL 
                END AS VARCHAR(50)) FzFlatRateStatusText,
                FRPLAN.DATUM,
                AUF.AuftragsNr,
                FRPLAN.BETRAGPLAN,
                FRPLAN.BETRAG,
                (Select ROUND((Select COALESCE(Sum(BetragPlan),
                0) From FzFlatRatePlan Where FzFlatRateID = FRPLAN.FzFlatRateID) - COALESCE(SUM(Betrag),
                0),
                2) From FzFlatRatePlan Where FzFlatRateID = FRPLAN.FzFlatRateID And Nummer <= FRPLAN.Nummer) Saldo
            From FzFlatRatePlan FRPLAN
              LEFT JOIN FzFlatRatePosition POS ON POS.FzFlatRatePositionID = FRPLAN.FzFlatRatePositionID
              LEFT JOIN FzFlatRateLeistung FL ON FL.FzFlatRateLeistungID = POS.FzFlatRateLeistungID
              LEFT JOIN Auftrag AUF on AUF.AuftragID = FRPLAN.AuftragID
            Where
              FRPLAN.FzFlatRateID = '.(int)$FZFlATRATEID.'
            Order By FRPLAN.Nummer
        '));
    }
    
    public function termin()
    {
        return $this->hasMany(Termin::class, 'auftragnr', 'auftragnr');
    }
    
    public function addon() 
    {
        return $this->hasMany(AuftragAddons::class, 'auftrag_id', 'id');
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
        
        return $wspakets->unique();
    }
}
