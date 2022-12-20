<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\MACS;

use Firebird\Eloquent\Model;
/**
 * Description of AuftragStatus
 *
 * @author DS
 */
class AuftragStatus extends Model
{

    //AUFTRAGSSTATUS
    protected $connection = 'macs';
    protected $table      = 'AUFTRAGSSTATUS';
    protected $primaryKey = 'STATUS';
    public $timestamps    = false;

}
