<?php


namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class FzMotorArt extends Model
{

    protected $connection = 'macs';
    protected $table      = 'FZMOTORART';
    protected $primaryKey = 'FZMOTORARTID';
    public $timestamps    = false;

    

}
