<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class FahrzeugStatus extends Model
{

    protected $connection = 'macs';
    protected $table      = 'FZSTATUS';
    protected $primaryKey = 'FZSTATUSID';
    public $timestamps    = false;

}
