<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

class Kunde extends Model
{

    public $timestamps	 = false;
    public $incrementing	 = false;
    protected $primaryKey	 = 'id';
    protected $table	 = 'checker_kunde';
    protected $fillable	 = [
	'id',
	'name',
	'address',
	'email',
	'phone',
	'kundennr',
	'anrede',
	'kurzname'
    ];

    public function auftrag()
    {
	return $this->belongsTo(Auftrag::class, 'auftrag_id');
    }

    public function comm()
    {
	return $this->hasMany(KundeComm::class, 'kunde_id', 'id');
    }

    public function dse()
    {
	return $this->hasMany(KundeDSE::class, 'kunde_id', 'id');
    }
    
    public function datenshutz()
    {
	return $this->hasOne(KundeDatenschutz::class, 'id', 'id');
    }

}
