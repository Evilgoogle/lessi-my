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
class Teil extends Model
{

    protected $connection	 = 'macs';
    protected $table	 = 'TEIL';
    protected $primaryKey	 = 'TEILID';
    public $timestamps	 = false;
    protected $fillable = ['HERSTELLERID','ETNR','BEZEICHNUNG','EKPREIS','UVPE'];

    public function hersteller()
    {
	return $this->hasOne(Hersteller::class, 'HERSTELLERID', 'HERSTELLERID');
    }

}
