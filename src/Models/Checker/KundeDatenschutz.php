<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

class KundeDatenschutz extends Model
{

    public $timestamps	 = false;
    public $incrementing	 = false;
    protected $primaryKey	 = 'id';
    protected $table	 = 'checker_kunde_datenschutz';
    protected $fillable	 = [
	'id',
	'brief',
	'mail',
	'sms',
        'telefon',
    ];
}
