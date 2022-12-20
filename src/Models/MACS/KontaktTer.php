<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class KontaktTer extends Model
{

    protected $connection = 'macs';
    protected $table      = 'KONTAKTERLAUBNISHISTO';
    protected $primaryKey = 'KONTAKTERLAUBNISHISTOID';
    public $timestamps    = false;

}
