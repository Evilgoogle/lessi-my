<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\MACS;

use Firebird\Eloquent\Model;
use App\Models\Dispo;

/**
 * Description of AuftragPosition
 *
 * @author DS
 */
class AuftragPosition extends Model
{

    protected $connection	 = 'macs';
    protected $table	 = 'AUFTRAGSPOSITION';
    protected $primaryKey	 = 'AUFTRAGSPOSITIONID';
    public $timestamps	 = false;

    const POSTYP_ERSATZTEIL = 1;

    public function rechnung()
    {
	return $this->belongsTo(AuftragRechnung::class, 'AUFTRAGRECHNUNGID', 'AUFTRAGRECHNUNGID');
    }

    public function auftrag()
    {
	return $this->belongsTo(Auftrag::class, 'AUFTRAGID', 'AUFTRAGID');
    }

    public function typ()
    {
	return $this->hasOne(PositionTyp::class, 'POSTYP', 'POSTYP');
    }

    public function teil()
    {
	return $this->hasOne(Teil::class, 'TEILID', 'TEILID');
    }

    public function paketPosition()
    {
	return $this->hasOne(WSPaketPosition::class, 'WSPAKETPOSITIONID', 'WSPAKETPOSITIONID');
    }

    public function hersteller()
    {
	return $this->hasOne(Hersteller::class, 'HERSTELLERID', 'HERSTELLERID');
    }

}
