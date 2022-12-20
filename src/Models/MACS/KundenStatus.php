<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class KundenStatus extends Model
{

    protected $connection = 'macs';
    protected $table      = 'KUNDENSTATUS';
    protected $primaryKey = 'KUNDENSTATUSID';
    public $timestamps    = false;

}
