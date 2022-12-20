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
class KDDatenschutz extends Model
{

    protected $connection = 'macs';
    protected $table = 'KDDATENSCHUTZ';
    protected $primaryKey = 'KUNDEID';
    public $timestamps = false;

    public function kunde()
    {
        return $this->hasOne(Kunde::class, 'KUNDEID', 'KUNDEID');
    }
    public function hersteller()
    {
        return $this->hasOne(Hersteller::class, 'HERSTELLERID', 'HERSTELLERID');
    }

}
