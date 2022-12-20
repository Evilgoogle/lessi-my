<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

class Fahrzeug extends Model
{

    public $timestamps	 = false;
    public $incrementing	 = false;
    protected $primaryKey	 = 'id';
    protected $table	 = 'checker_fahrzeug';
    protected $fillable	 = [
	'id',
	'vin',
	'mark',
	'model',
	'kmstand',
	'year',
	'auftrag_count',
	'fabre',
    ];

    public function auftrag()
    {
	return $this->belongsTo(Auftrag::class,'fahrzeug_id');
    }

}
