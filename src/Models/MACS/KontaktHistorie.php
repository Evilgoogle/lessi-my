<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class KontaktHistorie extends Model
{

    protected $connection = 'macs';
    protected $table      = 'KONTAKTHISTORIE';
    protected $primaryKey = 'KONTAKTHISTORIEID';
    public $timestamps    = false;

    public function art()
    {
	return $this->hasOne(KontaktHistorieArt::class, 'KONTAKTHISTORIEARTID', 'KONTAKTHISTORIEARTID');
    }
}
