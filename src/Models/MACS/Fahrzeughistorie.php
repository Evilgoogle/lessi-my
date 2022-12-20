<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class Fahrzeughistorie extends Model
{

    protected $connection	 = 'macs';
    protected $table	 = 'FAHRZEUGHISTORIE';
    protected $primaryKey	 = 'FAHRZEUGHISTORIEID';
    public $timestamps	 = false;

    const ART_KUNDENWAGEN		 = 0;
    const ART_LAGERWAGEN		 = 1;
    const ART_VORFEHRWAGEN	 = 2;
    const ART_TAGESZULASSUNG	 = 7;

    public function fzAusstattung()
    {
	return $this->hasMany(FahrzeugAusstattung::class, 'FAHRZEUGHISTORIEID');
    }

    public function leasingFinanz()
    {
	return $this->hasOne(LeasingFinanz::class, 'FAHRZEUGHISTORIEID');
    }

    public function fahrzeug()
    {
	return $this->hasOne(Fahrzeug::class, 'FAHRZEUGID', 'FAHRZEUGID');
    }

    public function ankauf()
    {
	return $this->hasOne(Ankauf::class, 'FAHRZEUGHISTORIEID');
    }

    public function standort()
    {
	return $this->hasOne(Standort::class, 'STANDORTID', 'STANDORTID');
    }

    public function ek_price_d()
    {
	return $this->DIFFERENZBESTEUERT == 1 ? $this->EKPREIS : $this->EKPREIS * 1.19;
    }

    public function unfallfrei()
    {
	return $this->hasOne(Unfallfrei::class, 'UNFALLFREIID', 'UNFALLFREIID');
    }

    public function nkp_herkunft()
    {
	return $this->hasOne(NKPHerkunft::class, 'NKPHERKUNFTID', 'NKPHERKUNFTID');
    }

    public function auftrag()
    {
	return $this->hasOne(Auftrag::class, 'AUFTRAGID');
    }

    public function mitarbeiter()
    {
	return $this->hasOne(Mitarbeiter::class, 'MITARBEITERID', 'EINKAEUFERGWID');
    }

    public function art()
    {
	    return $this->hasOne(FahrzeugArt::class, 'FAHRZEUGARTID', 'FAHRZEUGARTID');
    }

    public function fzFlatRate()
    {
	    return $this->hasMany(FzFlatRate::class, 'FAHRZEUGHISTORIEID', 'FAHRZEUGHISTORIEID');
    }

    public function termin()
    {
	    return $this->hasMany(Termin::class, 'FAHRZEUGHISTORIEID', 'FAHRZEUGHISTORIEID');
    }


    public function kunde()
    {
        return $this->belongsTo(Kunde::class, 'KUNDEID', 'KUNDEID');
    }

    public static function getCarInfoByNumber($carNumber)
    {
        /*$result = Fahrzeug::selectRaw('FAHRZEUG.*, FAHRZEUGHISTORIE.AUFTRAGID, FAHRZEUGHISTORIE.KMSTAND, FZSTATUS.FZSTATUSTEXT')
            ->leftJoin('FAHRZEUGHISTORIE', 'FAHRZEUGHISTORIE.FAHRZEUGHISTORIEID', 'FAHRZEUG.FAHRZEUGHISTORIEIDAKTUELL')
            ->leftJoin('FZSTATUS', 'FZSTATUS.FZSTATUSID', 'FAHRZEUG.FZSTATUSID')
            ->where(['FAHRZEUG.MANDANTID' => 1])
            //->where("FAHRZEUGHISTORIE.POLKENNZEICHEN", '=', $carNumber)
            ->whereRaw("FAHRZEUGHISTORIE.POLKENNZEICHEN = ?", $carNumber)
            //->whereRaw("UPPER(REPLACE(REPLACE(FAHRZEUGHISTORIE.POLKENNZEICHEN, ' ', ''), '-', '')) = ?", $carNumber)
            ->first();*/
        
        /*$result = Fahrzeughistorie::selectRaw('FAHRZEUG.*, FAHRZEUGHISTORIE.AUFTRAGID, FAHRZEUGHISTORIE.KMSTAND, FZSTATUS.FZSTATUSTEXT')
            ->join('FAHRZEUG', 'FAHRZEUG.FAHRZEUGID', '=', 'FAHRZEUGHISTORIE.FAHRZEUGID')
            ->join('FZSTATUS', 'FZSTATUS.FZSTATUSID', '=', 'FAHRZEUG.FZSTATUSID')
            ->where(['FAHRZEUG.MANDANTID' => 1])
            ->whereRaw("FAHRZEUGHISTORIE.POLKENNZEICHEN = ?", $carNumber)
            ->first();*/
        
        $result = Fahrzeug::selectRaw('FAHRZEUG.*, FAHRZEUGHISTORIE.KMSTAND, FZSTATUS.FZSTATUSTEXT')
            ->leftJoin('FAHRZEUGHISTORIE', 'FAHRZEUGHISTORIE.FAHRZEUGHISTORIEID', 'FAHRZEUG.FAHRZEUGHISTORIEIDAKTUELL')
            ->leftJoin('FZSTATUS', 'FZSTATUS.FZSTATUSID', 'FAHRZEUG.FZSTATUSID')
            ->where(['FAHRZEUG.MANDANTID' => 1])
            ->whereRaw("FAHRZEUGHISTORIE.POLKENNZEICHEN = ?", $carNumber)
            ->first();

        return $result;
    }


}
