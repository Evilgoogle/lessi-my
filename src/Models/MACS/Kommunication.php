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
class Kommunication extends Model
{

    protected $connection = 'macs';
    protected $table      = 'KOMMUNIKATION';
    protected $primaryKey = 'KOMMUNIKATIONID';
    public $timestamps    = false;

    public function adresse ()
    {
        return $this->hasOne(Adresse::class, 'ADRESSEID', 'ADRESSEID');
    }

    public function art ()
    {
        return $this->hasOne(KommArt::class, 'KOMM_ARTID', 'KOMM_ARTID');
    }

}
