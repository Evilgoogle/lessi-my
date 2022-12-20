<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class FahrzeugTyp extends Model
{

    protected $connection = 'macs';
    protected $table      = 'FAHRZEUGTYP';
    protected $primaryKey = 'FAHRZEUGTYPID';
    public $timestamps    = false;

}
