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
 * Description of Kunde
 *
 * @author DS
 */
class AdresseCopie extends Model
{

    protected $connection	 = 'default';
    protected $table	 = 'macs_adresse';
    protected $primaryKey	 = 'ADRESSEID';
    public $timestamps	 = false;
    protected $fillable = ['ADRESSEID','KURZNAME','ANREDEID','NAME1'];

    public function anrede()
    {
	return $this->hasOne(Anrede::class, 'ANREDEID', 'ANREDEID');
    }

    public function kommunications()
    {
	return $this->hasMany(KommunicationCopie::class, 'ADRESSEID', 'ADRESSEID');
    }

}
