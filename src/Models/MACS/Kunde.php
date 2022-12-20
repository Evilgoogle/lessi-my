<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

/**
 * Description of Kunde
 *
 * @author DS
 */
class Kunde extends Model
{

    protected $connection	 = 'macs';
    protected $table	 = 'KUNDE';
    protected $primaryKey	 = 'KUNDEID';
    public $timestamps	 = false;

    public function mietVertrag()
    {
	return $this->hasMany(MietVertrag::class, 'KUNDEID');
    }

    public function hmdErf()
    {
	return $this->hasMany(\App\Models\HMD\Erfassungen0008::class, 'Belegnummer1', 'KUNDENNR');
    }

    public function gruppe()
    {
	return $this->hasOne(NKPKundenGruppe::class, 'NKPKUNDENGRUPPEID', 'NKPKUNDENGRUPPEID');
    }

    public function adresse()
    {
	return $this->hasOne(Adresse::class, 'ADRESSEID', 'ADRESSEID');
    }

    public function auftrags()
    {
	return $this->hasMany(Auftrag::class, 'KUNDEID', 'KUNDEID');
    }

    public function fahrzeug_h()
    {
	return $this->hasMany(Fahrzeughistorie::class, 'VORBESITZERKUNDEID');
    }

    public function fahrzeug_histories()
    {
	return $this->hasMany(Fahrzeughistorie::class, 'KUNDEID');
    }

    public function kontaktTer()
    {
	return $this->hasMany(KontaktTer::class, 'KUNDEID', 'KUNDEID');
    }

    public function kontaktHistorie()
    {
	return $this->hasMany(KontaktHistorie::class, 'KUNDEID', 'KUNDEID');
    }

    public function typ()
    {
	return $this->hasOne(KundenTyp::class, 'KUNDENTYPID', 'KUNDENTYPID');
    }

    public function status()
    {
	return $this->hasOne(KundenStatus::class, 'KUNDENSTATUSID', 'KUNDENSTATUSID');
    }

    public function datenschutz()
    {
	return $this->hasMany(KDDatenschutz::class, 'KUNDEID', 'KUNDEID');
    }

    public function lDts()
    {
	return $this->hasOne(\App\Models\Datenshutz::class,'kunde_id','KUNDEID');
    }
}
