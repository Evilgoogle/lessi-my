<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class Standort extends Model
{

    protected $connection = 'macs';
    protected $table      = 'STANDORT';
    protected $primaryKey = 'STANDORTID';
    public $timestamps    = false;

}
