<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class Fahrzeug extends Model
{

    protected $connection = 'macs';
    protected $table = 'FAHRZEUG';
    protected $primaryKey = 'FAHRZEUGID';
    public $timestamps = false;

    /*
     * art
     */

    const ART_VORFEHRWAGEN = 2;
    const ART_TAGESZULASSUNG = 7;
    /*
     * status
     */
    const STATUS_IM_BESTAND = 2;
    const STATUS_VERKAUFT_KUNDENBESTAND = 4;

    public function gwCheck()
    {
        return $this->hasOne(\App\Models\ParkingMobile\GwCheckAcceptance::class, 'fin', 'FAHRGESTELLNUMMER');
    }

    public function parking()
    {
        return $this->hasOne(\App\Models\ParkingMobile\CarsParked::class, 'firebird_id', 'FAHRZEUGID');
    }

    public function kosyfaEkFinanz()
    {
        return $this->hasMany(\App\Models\Junge\KosyfaEkFinanz::class, 'vin', 'FAHRGESTELLNUMMER')->whereNull(
            'delete_date'
        )->where('status', 'endgültig ausgelag.');
    }

    public function logisticApp()
    {
        return $this->hasMany(\App\Models\ParkingMobile\LogisticCheck::class, 'fahrzeugid', 'FAHRZEUGID');
    }

    public function logisticAppLast()
    {
        return $this->hasOne(\App\Models\ParkingMobile\LogisticCheck::class, 'fahrzeugid', 'FAHRZEUGID')->orderBy(
            'updated_at',
            'desc'
        )->limit(1);
    }

    public function CGWeblineConfirmed()
    {
        return $this->hasOne(\App\Models\Junge\CGWeblineConfirmed::class, 'vin', 'FAHRGESTELLNUMMER');
    }

    public function autoCrm()
    {
        return $this->hasOne(\App\Models\AutoCRM\Lead::class, 'number', 'FAHRGESTELLNUMMER');
    }

    public function fzFlatRate()
    {
        return $this->hasOne(FzFlatRate::class, 'FAHRZEUGID');
    }

    public function emotivAnalog()
    {
        return \App\Models\Junge\Emotiv::
//                        whereNull('soldDate')->
        where('code', trim($this->MODELLNR))
            ->get();
    }

    public function mazdaeurAnalog()
    {
        return \App\Models\Junge\MazdaeurCom::
        whereNull('soldDate')
            ->where('msc', trim($this->MODELLNR))
            ->get();
    }

    public function eMotivZulassung()
    {
        return $this->hasOne(\App\Models\Junge\EMotivZulassung::class, 'vin', 'FAHRGESTELLNUMMER')->orderBy(
            'id',
            'desc'
        );
    }

    public function mobile_de()
    {
        try {
            return $this->hasOne(\App\Models\CustomPublished::class, 'fin', 'FAHRGESTELLNUMMER')
                ->where('is_published', '0')->orderBy('id', 'desc');
        } catch (Exception $e) {
            return null;
        }
    }

    public function mobile_de_actuell()
    {
        return $this->mobile_de()->where('is_published', '0')->where(
            'features_mobile',
            'like',
            '%"reserved";b:0%'
        )->first();
    }

    public function auftrags()
    {
        return $this->hasMany(Auftrag::class, 'FAHRZEUGID');
    }

    public function auftrags_after()
    {
        if ($this->fzh_actuell->LIEFERDAT) {
            return $this->auftrags()->where('ANNAHME', '>=', $this->fzh_actuell->LIEFERDAT)->get();
        }
    }

    public function fzh_actuell()
    {
        return $this->hasOne(Fahrzeughistorie::class, 'FAHRZEUGHISTORIEID', 'FAHRZEUGHISTORIEIDAKTUELL');
    }

    public function hersteller()
    {
        return $this->hasOne(Hersteller::class, 'HERSTELLERID', 'HERSTELLERID');
    }

    public function status()
    {
        return $this->hasOne(FahrzeugStatus::class, 'FZSTATUSID', 'FZSTATUSID');
    }

    public function art()
    {
        return $this->hasOne(FahrzeugArt::class, 'FAHRZEUGARTID', 'FAHRZEUGARTID');
    }

    public function motorArt()
    {
        return $this->hasOne(FzMotorArt::class, 'FZMOTORARTID', 'FZMOTORARTID');
    }

    public function typ()
    {
        return $this->hasOne(FahrzeugTyp::class, 'FAHRZEUGTYPID', 'FAHRZEUGTYPID');
    }

    public function fahrzeug_historie()
    {
        return $this->hasMany(Fahrzeughistorie::class, 'FAHRZEUGID');
    }

    public function kalkertrage()
    {
        return $this->hasMany(Kalkertrag::class, 'MODELLID', 'MODELLID')->where('KUNDEID', $this->KUNDEID);
    }

    public function letzte_kalkertrag()
    {
        return $this->kalkertrage()->orderBy('PLANVERKDATUM', 'desc')->first();
    }

    public function mietwagen()
    {
        return $this->hasOne(MietWagen::class, 'FAHRZEUGID', 'FAHRZEUGID');
    }

    public function filiale()
    {
        return $this->hasOne(Filiale::class, 'FILIALEID', 'FILIALEID');
    }

    public function saleFollowUp()
    {
        return $this->hasMany(\App\Models\GWNews\SaleFollowUp::class, 'fzid', 'FAHRZEUGID');
    }

    public function junge_cares()
    {
        return $this->hasMany(\App\Models\Junge\MgCare::class, 'VIN', 'FAHRGESTELLNUMMER');
    }

    public function modell()
    {
        return $this->hasOne(Modell::class, 'MODELLID', 'MODELLID');
    }
}
