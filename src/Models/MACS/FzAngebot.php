<?php


namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class FzAngebot extends Model
{

    protected $connection = 'macs';
    protected $table      = 'FZANGEBOT';
    protected $primaryKey = 'FZANGEBOTID';
    public $timestamps    = false;

    
    public function kalkertrag()
    {
        return $this->hasOne(Kalkertrag::class,'KALKERTRAGID','KALKERTRAGID');
    }
    
    public function positions()
    {
        return $this->hasMany(FzAngebotPos::class,'FZANGEBOTID','FZANGEBOTID');
    }
}
