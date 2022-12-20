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
class Mitarbeiter extends Model
{

    protected $connection	 = 'macs';
    protected $table	 = 'MITARBEITER';
    protected $primaryKey	 = 'MITARBEITERID';
    public $timestamps	 = false;

    
    public function user()
    {
	return $this->hasOne(\App\Models\UserManager\User::class,'macs_id');
    }
    
    public function scopeActive($query, \DateTime $date = null)
    {
	$date = $date ?? new \DateTime('today');
	return $query->where(function($q)use ($date)
		{
		    return $q->whereNull('AUSTRITTSDATUM')
				    ->orWhere('AUSTRITTSDATUM', '>=', $date);
		});
    }

    public function scopeNotAbsent($query, \DateTime $date = null)
    {
	$date = $date ?? new \DateTime('today');
	return $query->whereDoesntHave('stempel', function($q) use ($date)
		{
		    $arts = array_merge(range(16, 37), [44, 45, 48, 49]);
		    return $q->whereIn('STEMPELARTIDANFANG', $arts)
				    ->where('ZEITANFANG', '<=', $date)
				    ->where('ZEITENDE', '>=', $date);
		});
    }

    public function scopeAbsent($query, \DateTime $date = null, \DateTime $date2 = null)
    {
	$date	 = $date ?? new \DateTime('today');
	$date2	 = $date2 ?? $date;
	return $query->whereHas('stempel', function($q) use ($date,$date2)
		{
		    $arts = array_merge(range(16, 37), [44, 45, 48, 49]);
		    return $q->whereIn('STEMPELARTIDANFANG', $arts)
				    ->where('ZEITANFANG', '<=', $date2)
				    ->where('ZEITENDE', '>=', $date);
		});
    }

    public function zulassungEvents()
    {
	return $this->hasMany(\App\Models\Dispo\ZulassungEvents::class, 'annehmer_id');
    }

    public function stempel()
    {
	return $this->hasMany(Stempel::class, 'MITARBEITERID');
    }

    public function stempelToday(\DateTime $date = null)
    {
	$date = $date ?? new \DateTime('today');
	return $this->stempel()
			->where('ZEITANFANG', '<=', $date)
			->where('ZEITENDE', '>=', $date)->get();
	;
    }

    public function adresse()
    {
	return $this->hasOne(Adresse::class, 'ADRESSEID', 'ADRESSEID');
    }

    public function monteur()
    {
	return $this->hasOne(WSMonteur::class, 'MITARBEITERID', 'MITARBEITERID');
    }

    public function terminGruppes() {
        return $this->hasMany(TerminGruppeMitarbeiter::class, 'MITARBEITERID');
    }
}
