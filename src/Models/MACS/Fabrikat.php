<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class Fabrikat extends Model
{

    protected $connection	 = 'macs';
    protected $table	 = 'FABRIKAT';
    protected $primaryKey	 = 'FABRIKATID';
    public $timestamps	 = false;

    public function hersteller()
    {
	return $this->hasOne(Hersteller::class, 'HERSTELLERID', 'HERSTELLERID');
    }

}
