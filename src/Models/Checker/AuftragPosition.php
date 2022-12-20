<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of AuftragPosition
 *
 * @author rijen
 */
class AuftragPosition extends Model
{

    public $timestamps	 = false;
    public $incrementing	 = false;
    protected $primaryKey	 = 'id';
    protected $table	 = 'checker_auftrag_position';
    protected $fillable	 = [
	'id',
	'auftragnr',
	'positionstype',
	'bezeichnung',
	'typ',
	'menge',
	'preis',
	'betrag',
        'kostentrager',
        'bemerkung',
        'zahlart',
        'anteil'
    ];
}
