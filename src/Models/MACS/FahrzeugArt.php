<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class FahrzeugArt extends Model
{

    protected $connection = 'macs';
    protected $table      = 'FAHRZEUGART';
    protected $primaryKey = 'FAHRZEUGARTID';
    public $timestamps    = false;

}
