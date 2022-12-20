<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class FahrzeughistorieCopie extends Model
{

    public $timestamps    = false;
    public $incrementing  = false;
    protected $connection = 'default';
    protected $table      = 'macs_fahrzeughistorie';
    protected $primaryKey = 'FAHRZEUGHISTORIEID';
    protected $fillable   = [ 'FAHRZEUGHISTORIEID', 'FAHRZEUGID', 'UNFALLFREIID', 'UNFALLTEXT', 'ISUNFALL' ];

    public function fahrzeug ()
    {
        return $this->hasOne(Fahrzeug::class, 'FAHRZEUGID', 'FAHRZEUGID');
    }

    public function original ()
    {
        return $this->hasOne(Fahrzeughistorie::class, 'FAHRZEUGHISTORIEID', 'FAHRZEUGHISTORIEID');
    }

}
