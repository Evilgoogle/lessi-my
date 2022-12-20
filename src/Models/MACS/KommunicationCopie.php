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
class KommunicationCopie extends Model
{

    protected $connection	 = 'default';
    protected $table	 = 'macs_kommunication';
    protected $primaryKey	 = 'KOMMUNIKATIONID';
    public $timestamps	 = false;
    protected $fillable	 = ['KOMMUNIKATIONID', 'ADRESSEID', 'KOMM_TEXT', 'KOMM_ARTID','SUCHNR'];

    public function adresse()
    {
	return $this->hasOne(AdresseCopie::class, 'ADRESSEID', 'ADRESSEID');
    }

    public function art()
    {
	return $this->hasOne(KommArt::class, 'KOMM_ARTID', 'KOMM_ARTID');
    }

}
