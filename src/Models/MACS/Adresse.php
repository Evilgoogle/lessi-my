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
class Adresse extends Model
{

    protected $connection = 'macs';
    protected $table      = 'ADRESSE';
    protected $primaryKey = 'ADRESSEID';
    public $timestamps    = false;

    public function anrede ()
    {
        return $this->hasOne(Anrede::class, 'ANREDEID', 'ANREDEID');
    }

    public function kommunications ()
    {
        return $this->hasMany(Kommunication::class, 'ADRESSEID', 'ADRESSEID');
    }
    public function kunde ()
    {
        return $this->hasOne(Kunde::class, 'ADRESSEID', 'ADRESSEID');
    }

}
