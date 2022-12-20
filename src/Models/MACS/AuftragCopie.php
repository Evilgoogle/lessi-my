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
 * Description of Auftrag
 *
 * @author DS
 */
class AuftragCopie extends Model
{

    protected $connection = 'default';
    protected $table      = 'macs_auftrag';
    protected $primaryKey = 'AUFTRAGID';
    public $timestamps    = false;
    protected $fillable   = [  'AUFTRAGID','AUFTRAGSNR','HERSTELLERID','FAHRZEUGID','FGSTNR','FILIALEID','STATUS','KUNDEID','ANNEHMERID','ANNAHME','ANLAGEDAT' ];

    public function rechnungs ()
    {
        return $this->hasMany(AuftragRechnungCopie::class, 'AUFTRAGID', 'AUFTRAGID');
    }

    public function status ()
    {
        return $this->hasOne(AuftragStatus::class, 'STATUS', 'STATUS');
    }

    public function typ ()
    {
        return $this->hasOne(AuftragTyp::class, 'TYP', 'TYP');
    }

    public function art ()
    {
        return $this->hasOne(AuftragArt::class, 'ART', 'ART');
    }

    public function positions ()
    {
        return $this->hasMany(AuftragPosition::class, 'AUFTRAGID')->orderBy('POSITIONSNR');
    }

    public function hersteller ()
    {
        return $this->hasOne(Hersteller::class, 'HERSTELLERID', 'HERSTELLERID');
    }

    public function fahrzeug ()
    {
        return $this->hasOne(Fahrzeug::class, 'FAHRZEUGID', 'FAHRZEUGID');
    }

    public function kunde ()
    {
        return $this->hasOne(Kunde::class, 'KUNDEID', 'KUNDEID');
    }

        public function mitarbeiter ()
    {
        return $this->hasOne(Mitarbeiter::class, 'MITARBEITERID', 'ANNEHMERID');
    }
}
