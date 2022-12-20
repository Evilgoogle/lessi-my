<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class Kalkertrag extends Model
{

    protected $connection = 'macs';
    protected $table      = 'KALKERTRAG';
    protected $primaryKey = 'KALKERTRAGID';
    public $timestamps    = false;

    public function pos ()
    {
        return $this->hasMany(KalkertragPos::class, 'KALKERTRAGID');
    }

    public function angebot ()
    {
        return $this->hasOne(FzAngebot::class, 'KALKERTRAGID', 'KALKERTRAGID');
    }

}
