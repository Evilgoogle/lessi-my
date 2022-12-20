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
class WSTagePlan extends Model
{

    protected $connection = 'macs';
    protected $table      = 'WSTAGESPLAN';
    protected $primaryKey = 'WSTAGESPLANID';
    public $timestamps    = false;

    public function monteur ()
    {
        return $this->hasOne(WSMonteur::class, 'WSMONTEURID', 'WSMONTEURID');
    }

}