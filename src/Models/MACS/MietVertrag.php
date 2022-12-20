<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class MietVertrag extends Model
{

    protected $connection	 = 'macs';
    protected $table	 = 'MIETVERTRAG';
    protected $primaryKey	 = 'MIETVERTRAGID';
    public $timestamps	 = false;

    public function kunde()
    {
	return $this->hasOne(Kunde::class,'KUNDEID','KUNDEID');
    }
    
    public function mitarbeiter()
    {
	return $this->hasOne(Mitarbeiter::class,'MITARBEITERID','MITARBEITERID');
    }
    
    public function wagen()
    {
	return $this->hasOne(MietWagen::class,'MIETWAGENID','MIETWAGENID');
    }
    
}
