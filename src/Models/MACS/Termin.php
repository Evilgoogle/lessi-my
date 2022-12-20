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
class Termin extends Model
{

    protected $connection = 'macs';
    protected $table = 'TERMIN';
    protected $primaryKey = 'TERMINID';
    public $timestamps = false;

    protected $appends = ['startFormatted', 'endFormatted'];

    protected $fillable = [
        'TERMINID',
        "BETRIFFT",
        "PRIVAT",
        "TERMINAM",
        "TERMINUM",
        "ERLEDIGTAM",
        "ERLEDIGTUM",
        "BEMERKUNG",
        "MITARBEITERID",
        "ANGELEGTVON",
        "FAHRZEUGHISTORIEID",
        "TERMINARTID",
        "KUNDEID",
        "CREATED",
        "MANDANTID",
        "AUFTRAGID",
        "ERINNERN",
        "ERINNERNMINUTEN",
        "AUFTRAGIDALT",
        "AUSGELAGERT",
        "ISAUFGABE",
        "MEETINGID",
        "SHOWWSTERMIN",
        "TERMINBIS",
        "KUNDE_KDKONTAKTPAKETID",
        "TERMINSERIEID",
        "ISPROTOTYP",
        "SERIENORIGINALDATE",
        "FILIALEID",

    ];

    public function art()
    {
        return $this->hasOne(TerminArt::class, 'TERMINARTID', 'TERMINARTID');
    }

    public function mitarbeiter()
    {
        return $this->hasOne(Mitarbeiter::class, 'MITARBEITERID', 'MITARBEITERID');
    }

    public function angelegtVon()
    {
        return $this->hasOne(Mitarbeiter::class, 'MITARBEITERID', 'ANGELEGTVON');
    }

    public function kunde()
    {
        return $this->belongsTo(Kunde::class, 'KUNDEID', 'KUNDEID');
    }

    public function auftrag ()
    {
        return $this->belongsTo(Auftrag::class, 'AUFTRAGID', 'AUFTRAGID');
    }

    public function getStartFormattedAttribute()
    {
        $terminam = \DateTime::createFromFormat('Y-m-d H:i:s', $this->TERMINAM);
        $date = $terminam->format('Y-m-d');
        if ($this->TERMINUM) {
            $date .= 'T'.$this->TERMINUM;
        }

        return $date;
    }

    public function getEndFormattedAttribute()
    {
        if (!$this->TERMINBIS) {
            return null;
        }

        $terminam = \DateTime::createFromFormat('Y-m-d H:i:s', $this->TERMINAM);
        $date = $terminam->format('Y-m-d');
        $date .= 'T'.$this->TERMINBIS;

        return $date;
    }
}
