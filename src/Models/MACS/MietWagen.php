<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class MietWagen extends Model
{

    protected $connection	 = 'macs';
    protected $table	 = 'MIETWAGEN';
    protected $primaryKey	 = 'MIETWAGENID';
    public $timestamps	 = false;

    public function fahrzeug()
    {
	return $this->hasOne(Fahrzeug::class, 'FAHRZEUGID', 'FAHRZEUGID');
    }

    public function vertrag()
    {
	return $this->hasOne(MietVertrag::class, 'MIETWAGENID', 'MIETWAGENID');
    }

}
