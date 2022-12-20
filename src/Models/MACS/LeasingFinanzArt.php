<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class LeasingFinanzArt extends Model
{

    protected $connection = 'macs';
    protected $table      = 'LEASFINZART';
    protected $primaryKey = 'LEASFINZARTID';
    public $timestamps    = false;

}
