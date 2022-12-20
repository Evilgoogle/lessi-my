<?php

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

class WSPaketPosition extends Model
{

    protected $connection = 'macs';
    protected $table      = 'WSPAKETPOSITION';
    protected $primaryKey = 'WSPAKETPOSITIONID';
    public $timestamps    = false;

    public function paket ()
    {
        return $this->hasOne(WSPaket::class,'WSPAKETID','WSPAKETID');
    }

}
