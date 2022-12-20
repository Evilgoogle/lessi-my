<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class Modell extends Model
{

    protected $connection	 = 'macs';
    protected $table	 = 'MODELL';
    protected $primaryKey	 = 'MODELLID';
    public $timestamps	 = false;

    
    public function ausstattung()
    {
	return $this->belongsToMany(Ausstattung::class,'MODELL_AUSSTATTUNG','MODELLID','AUSSTATTUNGID');
    }
}
