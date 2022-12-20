<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\LeasBack;

use Firebird\Eloquent\Model;

/**
 * Description of Fahrzeug
 *
 * @author rijen
 */
class DirektannahmeInspektion extends Model
{
    protected $connection = 'info';
    protected $table = 'leasing_datas_direktannahme_inspektion';
    public $timestamps = false;
    
    static function init($leasback_id) 
    {
        $set = new DirektannahmeInspektion();
        $set->leasback_id = $leasback_id;
        $set->type = 'Wartungfrei';
        $set->save();
    }
}
