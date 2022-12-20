<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class FahrzeugAusstattung extends Model
{

    protected $connection	 = 'macs';
    protected $table	 = 'FAHRZEUGAUSSTATTUNG';
//    protected $primaryKey	 = null;
    public $timestamps	 = false;

    public function ausstattung()
    {
	return $this->hasOne(Ausstattung::class, 'AUSSTATTUNGID','AUSSTATTUNGID');
    }

}
