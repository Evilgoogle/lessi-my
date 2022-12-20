<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\MACS;

//ZEITPLANAKTUELL
use Firebird\Eloquent\Model;

class ZeitPlanActuell extends Model
{

    protected $connection = 'macs';
    protected $table      = 'ZEITPLANAKTUELL';
    protected $primaryKey = 'ZEITPLANAKTUELLID';
    public $timestamps    = false;


}
