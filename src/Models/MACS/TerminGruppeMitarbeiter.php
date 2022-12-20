<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class TerminGruppeMitarbeiter extends Model
{

    protected $connection = 'macs';
    protected $table = 'TERMINGRUPPE_MITARBEITER';
    protected $primaryKey = null;
    public $timestamps = false;


}
