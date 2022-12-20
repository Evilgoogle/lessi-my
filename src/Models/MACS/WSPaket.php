<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class WSPaket extends Model
{

    protected $connection = 'macs';
    protected $table      = 'WSPAKET';
    protected $primaryKey = 'WSPAKETID';
    public $timestamps    = false;

}
