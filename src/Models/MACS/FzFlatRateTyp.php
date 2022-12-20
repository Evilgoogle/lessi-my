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
class FzFlatRateTyp extends Model
{

    protected $connection = 'macs';
    protected $table      = 'FZFLATRATETYP';
    protected $primaryKey = 'FZFLATRATETYPID';
    public $timestamps    = false;

}
