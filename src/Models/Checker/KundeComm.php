<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

class KundeComm extends Model
{

    public $timestamps	 = false;
    public $incrementing	 = false;
    protected $primaryKey	 = 'id';
    protected $table	 = 'checker_kunde_communications';
    protected $fillable	 = [
	'id',
	'kunde_id',
	'type_id',
	'type',
	'val',
    ];

    public function kunde()
    {
	return $this->belongsTo(Kunde::class,'kunde_id');
    }

}
