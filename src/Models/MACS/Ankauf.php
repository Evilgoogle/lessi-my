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
class Ankauf extends Model
{

    protected $connection	 = 'macs';
    protected $table	 = 'ANKAUF';
    protected $primaryKey	 = 'ANKAUFID';
    public $timestamps	 = false;

    public function fahrzeug_historie()
    {
	return $this->hasOne(Fahrzeughistorie::class, 'FAHRZEUGHISTORIEID', 'FAHRZEUGHISTORIEID');
    }

    public function kontaktHistorie()
    {
	return $this->hasMany(KontaktHistorie::class, 'ANKAUFID', 'ANKAUFID');
    }

    public function status()
    {
	return $this->hasOne(AnkaufStatus::class, 'ANKAUFSTATUSID', 'ANKAUFSTATUSID');
    }

}
