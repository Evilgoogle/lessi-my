<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class Hersteller extends Model
{

    protected $connection = 'macs';
    protected $table      = 'HERSTELLER';
    protected $primaryKey = 'HERSTELLERID';
    public $timestamps    = false;

    public function auftrage()
    {
        return $this->hasMany(Auftrag::class,'HERSTELLERID','HERSTELLERID');
    }
}
