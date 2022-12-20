<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

class KundeDSE extends Model
{

    public $timestamps	 = false;
    public $incrementing	 = false;
    protected $primaryKey	 = 'id';
    protected $table	 = 'checker_kunde_dse';
    protected $fillable	 = [
	'id',
	'kunde_id',
	'date',
	'type',
	'val',
    ];

    public function kunde()
    {
	return $this->belongsTo(Kunde::class,'kunde_id');
    }

}
