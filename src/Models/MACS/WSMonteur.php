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
class WSMonteur extends Model
{

    protected $connection = 'macs';
    protected $table      = 'WSMONTEUR';
    protected $primaryKey = 'WSMONTEURID';
    public $timestamps    = false;

    public function reparatur_plan ()
    {
        return $this->belongsToMany(WSReparaturPlan::class, WSTagePlan::class, 'WSMONTEURID', 'WSREPARATURPLANID')
                        ->withPivot([ 'WSMONTEURID', 'VON', 'BIS', 'ISTFEST' ]);
    }

    public function mitarbeiter ()
    {
        return $this->hasOne(Mitarbeiter::class, 'MITARBEITERID', 'MITARBEITERID')
                        ->with('adresse:KURZNAME,ADRESSEID');
    }

    public function gruppe ()
    {
        return $this->hasOne(WSGruppe::class, 'WSGRUPPEID', 'WSGRUPPEID');
    }

    public function stempel ()
    {
        return $this->hasMany(Stempel::class, 'MITARBEITERID', 'MITARBEITERID');
    }

    public function zeitplan ()
    {
        return $this->hasMany(ZeitPlanActuell::class, 'MITARBEITERID', 'MITARBEITERID');
    }

    public function zeitplan_by_date (\DateTime $date)
    {
        return $this->hasMany(ZeitPlanActuell::class, 'MITARBEITERID', 'MITARBEITERID')
                        ->where('PLANDATUM', $date->format('d.m.Y'));
    }

}
