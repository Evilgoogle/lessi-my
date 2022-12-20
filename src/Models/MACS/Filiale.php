<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class Filiale extends Model
{

    protected $connection	 = 'macs';
    protected $table	 = 'FILIALE';
    protected $primaryKey	 = 'FILIALEID';
    public $timestamps	 = false;

    public function adresse()
    {
	return $this->hasOne(Adresse::class, 'ADRESSEID','ADRESSEID');
    }

}
