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
class WSReparaturPlan extends Model
{

    protected $connection = 'macs';
    protected $table      = 'WSREPARATURPLAN';
    protected $primaryKey = 'WSREPARATURPLANID';
    public $timestamps    = false;

    public function auftrag ()
    {
        return $this->hasOne(Auftrag::class, 'AUFTRAGID', 'AUFTRAGID');
    }
    public function tageplan ()
    {
        return $this->hasOne(WSTagePlan::class, 'WSREPARATURPLANID', 'WSREPARATURPLANID');
    }

    public function tageplan_auftag ()
    {
        return $this->belongsToMany(Auftrag::class, WSTagePlan::class, 'WSREPARATURPLANID', 'AUFTRAGID')
                        ->withPivot([ 'WSMONTEURID', 'VON', 'BIS', 'ISTFEST' ]);
    }

    public function gruppe ()
    {
        return $this->hasOne(WSGruppe::class, 'WSGRUPPEID', 'WSGRUPPEID');
    }

    public function auftrag_position()
    {
        return $this->hasMany(AuftragPosition::class, 'AUFTRAGID', 'AUFTRAGID')
            ->where('POSTYP', 1);
    }

}
