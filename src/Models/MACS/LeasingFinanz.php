<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class LeasingFinanz extends Model
{

    protected $connection = 'macs';
    protected $table      = 'LEASINGFINANZ';
    protected $primaryKey = 'LEASINGFINANZID';
    public $timestamps    = false;

    public function lfArt ()
    {
        return $this->hasOne(LeasingFinanzArt::class, 'LEASFINZARTID','LEASFINZARTID');
    }

    public function art ()
    {
        return $this->hasOne(FinanzArt::class, 'FINANZARTID','FINANZARTID');
    }

    public function lfKunde()
    {
        return $this->hasOne(Kunde::class,'KUNDEID','LFKUNDEID');
    }
}
