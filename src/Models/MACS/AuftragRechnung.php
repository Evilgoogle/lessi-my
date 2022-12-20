<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

/**
 * Description of AuftragRechnung
 *
 * @author DS
 */
class AuftragRechnung extends Model
{

    protected $connection	 = 'macs';
    protected $table	 = 'AUFTRAGRECHNUNG';
    protected $primaryKey	 = 'AUFTRAGRECHNUNGID';
    public $timestamps	 = false;

    public function typ()
    {
	return $this->hasOne(AuftragTyp::class, 'TYP', 'TYP');
    }

    public function status()
    {
	return $this->hasOne(AuftragStatus::class, 'STATUS', 'STATUS');
    }

    public function kunde()
    {
	return $this->hasOne(Kunde::class, 'KUNDEID','KUNDEID');
    }

    public function buchung()
    {
	return $this->hasOne(Buchung::class,'BUCHUNGID','BUCHUNGID');
    }
    public function nkp_buchung()
    {
	return $this->hasOne(NKPBuchung::class,'NKPBUCHUNGID','NKPBUCHUNGID');
    }
}
