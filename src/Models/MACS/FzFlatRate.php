<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\MACS;

use Firebird\Eloquent\Model;

/**
 * Description of Kunde
 *
 * @author DS
 */
class FzFlatRate extends Model
{

    protected $connection = 'macs';
    protected $table = 'FZFLATRATE';
    protected $primaryKey = 'FZFLATRATEID';
    public $timestamps = false;

    public function fahrzeug()
    {
        return $this->hasOne(Fahrzeug::class, 'FAHRZEUGID', 'FAHRZEUGID');
    }

    public function plan()
    {
        return $this->hasMany(FzFlatRatePlan::class, 'FZFLATRATEID', 'FZFLATRATEID');
    }

    public function status()
    {
        return $this->hasOne(FzFlatRateStatus::class, 'FZFLATRATESTATUSID', 'FZFLATRATESTATUSID');
    }

    public function typ()
    {
        return $this->hasOne(FzFlatRateTyp::class, 'FZFLATRATETYPID', 'FZFLATRATETYPID');
    }

    public function auftrag()
    {
        return $this->hasOne(Auftrag::class, 'AUFTRAGID', 'AUFTRAGID');
    }

}
