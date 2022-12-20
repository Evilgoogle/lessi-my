<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class KontaktHistorieArt extends Model
{

    protected $connection = 'macs';
    protected $table      = 'KONTAKTHISTORIEART';
    protected $primaryKey = 'KONTAKTHISTORIEARTID';
    public $timestamps    = false;

}
