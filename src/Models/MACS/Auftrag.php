<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

/**
 * Description of Auftrag
 *
 * @author DS
 */
class Auftrag extends Model
{

    const TYP_EXTERN = 0;
    const TYP_INTERN = 1;
    const TYP_GARANTIE = 2;

    /*
     * status
     */
    const STATUS_OFFEN = 1;
    const STATUS_FAKTURIERT = 2;
    const STATUS_GUTGESCHRIEBEN = 3;
    const STATUS_ANGEBOT = 4;
    const STATUS_ANGEBOT_ABGL = 5;

    /*
     * art
     */
    const ART_ANGEBOT = -1;
    const ART_WS_KOSTEN = 0;
    const ART_WS = 1;
    const ART_THEKEN = 2;
    const ART_MAN = 3;
    const ART_FZANKAUF = 4;

    protected $connection = 'macs';
    protected $table = 'AUFTRAG';
    protected $primaryKey = 'AUFTRAGID';
    public $timestamps = false;

    protected $casts = [
        'ANNAHME' => 'datetime',
        'ANLIEFERUNG' => 'datetime',
        'NOTIZ' => 'blob',
    ];

    public function maxRepUmfang()
    {
        $maxDate = null;
        $r = $this->reparatur_plan->sortBy('DATUM', 'DESC')->first();
        if ($r) {
            $maxDate = $r->DATUM;
        }

        return $maxDate;
    }

    public function kontaktHistorie()
    {
        return $this->hasMany(KontaktHistorie::class, 'AUFTRAGID', 'AUFTRAGID');
    }


    public function logbuchs()
    {
        return $this->hasMany(Logbuch::class, 'AUFTRAGSNR', 'AUFTRAGSNR');
    }


    public function scopeART4($query)
    {
        return $query->where('ART', 4);
    }

    public function fahrzeughistorie()
    {
        return $this->hasOne(Fahrzeughistorie::class, 'FAHRZEUGHISTORIEID', 'FAHRZEUGHISTORIEID');
    }

    public function cockpitComments()
    {
        return $this->hasMany(\App\Models\Dispo\Comment::class, 'auftrag_id', 'AUFTRAGID');
    }

    public function innerAuftrage()
    {
        return $this->hasMany(\App\Models\Dispo\InnerAuftrag::class, 'auftrag_id', 'AUFTRAGID');
    }

    public function vkAkte()
    {
        return $this->hasOne(VKAkte::class, 'AUFTRAGID', 'AUFTRAGID');
    }

    public function cockpitStatus()
    {
        return $this->hasMany(\App\Models\Dispo\CockpitStatus::class, 'auftrag_id', 'AUFTRAGID');
    }

    public function repairItem()
    {
        return $this->hasMany(\App\Models\Repair\Item::class, 'FGSTNR', 'FGSTNR')->where('finished', 0);
    }

    public function dispoCredits()
    {
        return $this->hasMany(\App\Models\Dispo\Credit::class, 'auftrag_id');
    }

    public function zulassungEvent()
    {
        return $this->hasOne(\App\Models\Dispo\ZulassungEvents::class, 'auftrag_id');
    }

    public function dispoChecklist()
    {
        return $this->hasMany(\App\Models\Dispo\Checklist::class, 'auftrag_id');
    }

    public function getDispoChecklist()
    {
        $items = \App\Models\Dispo\Checklist::where('auftrag_id', $this->AUFTRAGID)->get();
        $ret = [];
        foreach ($items as $item) {
            $ret[$item->key] = $item->value;
        }

        return $ret;
    }

    public function getKINote()
    {
        $items = \App\Models\Dispo\KINote::where('auftrag_id', $this->AUFTRAGID)->get();
        $ret = [];
        foreach ($items as $item) {
            $ret[$item->key] = $item->value;
        }

        return $ret;
    }

    public function dispoZulassung()
    {
        return $this->hasOne(\App\Models\Dispo\Zulassung::class, 'auftrag_id');
    }

    public function dispoZahlplan()
    {
        return $this->hasMany(\App\Models\Dispo\Zahlplan::class, 'auftrag_id');
    }

    public function dispoAuftrag()
    {
        return $this->hasOne(\App\Models\Dispo\AuftragOverload::class, 'auftrag_id');
    }

    public function dispoPos()
    {
        return $this->hasMany(\App\Models\Dispo\AuftragPosition::class, 'auftrag_id');
    }

//    public function ankaufe()
//    {
//
//	$fzh_with_ankauf = $this
//		->kunde->fahrzeug_h()
//		->has('ankauf')
//		->with('fahrzeug')
//		->get();
//
//	$ankaufe = [];
//
//	foreach ($fzh_with_ankauf as $fzh)
//	{
//
//	    if ($fzh->ankauf->ANKAUFSTATUSID == 1)
//	    {
//		$ankaufe[] = $fzh;
//	    }
//	}
//	return collect($ankaufe);
//    }

    public function kalkertrag()
    {
        return $this->hasOne(Kalkertrag::class, 'KALKERTRAGID', 'KALKERTRAGID');
    }

    public function stempel()
    {
        return $this->hasMany(Stempel::class, 'AUFTRAGID', 'AUFTRAGID');
    }

    public function auftrags()
    {
        return $this->hasMany(Auftrag::class, 'FZAUFTRAGID', 'AUFTRAGID');
    }

    public function reparatur_plan()
    {
        return $this->hasMany(WSReparaturPlan::class, 'AUFTRAGID', 'AUFTRAGID');
    }

    public function reparatur_plan_with_tageplan()
    {
        return $this->hasMany(WSReparaturPlan::class, 'AUFTRAGID', 'AUFTRAGID')->with('tageplan');
    }

    public function rechnungs()
    {
        return $this->hasMany(AuftragRechnung::class, 'AUFTRAGID', 'AUFTRAGID');
    }

    public function status()
    {
        return $this->hasOne(AuftragStatus::class, 'STATUS', 'STATUS');
    }

    public function filiale()
    {
        return $this->hasOne(Filiale::class, 'FILIALEID', 'FILIALEID');
    }

    public function typ()
    {
        return $this->hasOne(AuftragTyp::class, 'TYP', 'TYP');
    }

    public function art()
    {
        return $this->hasOne(AuftragArt::class, 'ART', 'ART');
    }

    public function positions()
    {
        return $this->hasMany(AuftragPosition::class, 'AUFTRAGID')->orderBy('POSITIONSNR');
    }

    public function positionsMengeSum()
    {
        return $this->hasMany(AuftragPosition::class, 'AUFTRAGID')
            ->sum('MENGE');
    }

    public function hersteller()
    {
        return $this->hasOne(Hersteller::class, 'HERSTELLERID', 'HERSTELLERID');
    }

    public function fahrzeug()
    {
        return $this->hasOne(Fahrzeug::class, 'FAHRZEUGID', 'FAHRZEUGID');
    }

    public function kunde()
    {
        return $this->hasOne(Kunde::class, 'KUNDEID', 'KUNDEID');
    }

    public function adresse()
    {
        return $this->hasOne(Adresse::class, 'ADRESSEID', 'ADRESSEID');
    }

    /**
     * @return type
     * @deprecated use annehmer
     */
    public function mitarbeiter()
    {
        return $this->hasOne(Mitarbeiter::class, 'MITARBEITERID', 'ANNEHMERID');
    }

    public function annehmer()
    {
        return $this->hasOne(Mitarbeiter::class, 'MITARBEITERID', 'ANNEHMERID');
    }

    public function abgeber()
    {
        return $this->hasOne(Mitarbeiter::class, 'MITARBEITERID', 'ABGEBERID');
    }

    public function monteur()
    {
        return $this->hasOne(Mitarbeiter::class, 'MITARBEITERID', 'MONTEURID');
    }

    public function anlageben()
    {
        return $this->hasOne(Mitarbeiter::class, 'SYSTEMID', 'ANLAGEBENID');
    }

    public function aenderben()
    {
        return $this->hasOne(Mitarbeiter::class, 'SYSTEMID', 'AENDERBENID');
    }

    public function reparaturArt()
    {
        return $this->hasOne(ReparaturArt::class, 'REPARATURARTID', 'REPARATURARTID');
    }

    static public function search($string)
    {
        return Auftrag::select(
            'AUFTRAG.AUFTRAGID',
            'AUFTRAG.AUFTRAGSNR',
            'f.FAHRGESTELLNUMMER',
            'ka.NAME1'
        )
            ->leftJoin('FAHRZEUG as f', 'f.FAHRZEUGID', '=', 'AUFTRAG.FAHRZEUGID')
            ->leftJoin('KUNDE as k', 'k.KUNDEID', '=', 'AUFTRAG.KUNDEID')
            ->leftJoin('ADRESSE as ka', 'ka.ADRESSEID', '=', 'k.ADRESSEID')
            ->where(function ($q) use ($string) {
                $q->where('AUFTRAG.AUFTRAGSNR', 'like', '%'.$string.'%')
                    ->orwhere('f.FAHRGESTELLNUMMER', 'like', '%'.$string.'%')
                    ->orwhere('ka.NAME1', 'like', '%'.$string.'%');
            })
            ->limit(10)
            ->get();
    }

    public function checker_termin()
    {
        return $this->hasMany(\App\Models\Checker\Termin::class, 'auftragnr', 'AUFTRAGSNR');
    }

    public function termin()
    {
        return $this->hasOne(Termin::class, 'AUFTRAGID', 'AUFTRAGID');
    }

    public function fzFlatRate()
    {
        return $this->hasMany(FzFlatRate::class, 'AUFTRAGID', 'AUFTRAGID');
    }
}
