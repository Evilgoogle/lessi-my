<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class Unfallfrei extends Model
{

    protected $connection = 'macs';
    protected $table      = 'UNFALLFREI';
    protected $primaryKey = 'UNFALLFREIID';
    public $timestamps    = false;

}
