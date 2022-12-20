<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\MACS;

//use Firebird\Eloquent\Model;
use Illuminate\Database\Eloquent\Model;

/**
 * Description of AuftragRechnung
 *
 * @author DS
 */
class AuftragRechnungCopie extends Model
{

//    protected $connection = 'macs';
    protected $connection = 'default';
    protected $table      = 'macs_auftrag_rechnung';
    protected $primaryKey = 'AUFTRAGRECHNUNGID';
    public $timestamps    = false;
     protected $fillable   = ['AUFTRAGRECHNUNGID','AUFTRAGID','NETTOBETRAG','MWSTBETRAG','BRUTTOBETRAG' ];

    public function typ ()
    {
        return $this->hasOne(AuftragTyp::class, 'TYP', 'TYP');
    }

    public function status ()
    {
        return $this->hasOne(AuftragStatus::class, 'STATUS', 'STATUS');
    }

}
