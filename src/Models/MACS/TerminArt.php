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
class TerminArt extends Model
{

    protected $connection	 = 'macs';
    protected $table	 = 'TERMINART';
    protected $primaryKey	 = 'TERMINARTID';
    public $timestamps	 = false;


}
