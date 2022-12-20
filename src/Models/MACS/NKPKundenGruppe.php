<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class NKPKundenGruppe extends Model
{

    protected $connection = 'macs';
    protected $table      = 'NKPKUNDENGRUPPE';
    protected $primaryKey = 'NKPKUNDENGRUPPEID';
    public $timestamps    = false;

}
