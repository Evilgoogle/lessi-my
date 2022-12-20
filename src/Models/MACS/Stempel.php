<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

/**
 * Description of Auftrag
 *
 * @author DS
 */
class Stempel extends Model
{

    protected $connection = 'macs';
    protected $table      = 'STEMPEL';
    protected $primaryKey = 'STEMPELID';
    public $timestamps    = false;

    public function auftrag()
    {
        return $this->belongsTo(Auftrag::class, 'AUFTRAGID', 'AUFTRAGID');
    }

    public function mitarbeiter()
    {
        return $this->hasOne(Mitarbeiter::class, 'MITARBEITERID', 'MITARBEITERID')
            ->with('adresse:KURZNAME,ADRESSEID');
    }

    public function monteur()
    {
        return $this->hasOne(WSMonteur::class, 'MITARBEITERID', 'MITARBEITERID');
    }

    public function artStart()
    {
        return $this->hasOne(StempelArt::class, 'STEMPELARTID', 'STEMPELARTIDANFANG');
    }
    public function artEnd()
    {
        return $this->hasOne(StempelArt::class, 'STEMPELARTID', 'STEMPELARTIDENDE');
    }
}
