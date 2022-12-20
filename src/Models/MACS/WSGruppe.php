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
class WSGruppe extends Model
{

    protected $connection = 'macs';
    protected $table      = 'WSGRUPPE';
    protected $primaryKey = 'WSGRUPPEID';
    public $timestamps    = false;

    public function reparatur_plan ()
    {
        return $this->belongsTo(WSReparaturPlan::class, 'WSGRUPPEID', 'WSGRUPPEID');
    }

    public function monteur ()
    {
        return $this->hasOne(WSMonteur::class, 'WSGRUPPEID', 'WSGRUPPEID')
                        ->with('mitarbeiter');
    }

}
