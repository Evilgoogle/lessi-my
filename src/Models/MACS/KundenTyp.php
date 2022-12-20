<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class KundenTyp extends Model
{

    protected $connection = 'macs';
    protected $table      = 'KUNDENTYP';
    protected $primaryKey = 'KUNDENTYPID';
    public $timestamps    = false;

}
