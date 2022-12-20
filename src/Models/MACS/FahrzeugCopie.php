<?php

namespace App\Models\MACS;

use Illuminate\Database\Eloquent\Model;

class FahrzeugCopie extends Model
{

    public $timestamps    = false;
    public $incrementing  = false;
    protected $connection = 'default';
    protected $table      = 'macs_fahrzeug';
    protected $primaryKey = 'FAHRZEUGID';
    protected $fillable   = [ 'FAHRZEUGID', 'FILIALEID', 'FZSTATUSID', 'NOTIZ', 'FAHRZEUGHISTORIEIDAKTUELL' ];

    public function fzh_actuell ()
    {
        return $this->hasOne(FahrzeughistorieCopie::class, 'FAHRZEUGHISTORIEID', 'FAHRZEUGHISTORIEIDAKTUELL');
    }

    public function fzhs ()
    {
        return $this->hasMany(FahrzeughistorieCopie::class, 'FAHRZEUGID', 'FAHRZEUGID');
    }

    public function original ()
    {
        return $this->hasOne(Fahrzeug::class, 'FAHRZEUGID', 'FAHRZEUGID');
    }

}
