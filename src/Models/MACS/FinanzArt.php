<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class FinanzArt extends Model
{

    protected $connection = 'macs';
    protected $table      = 'FINANZART';
    protected $primaryKey = 'FINANZARTID';
    public $timestamps    = false;

}
