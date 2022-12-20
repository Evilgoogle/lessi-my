<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

class AuftragKundenannahmeInfoTag extends Model
{
    public $timestamps	 = false;
    protected $primaryKey	 = 'id';
    protected $table	 = 'checker_auftrag_kundenannahme_infotags';
    protected $fillable	 = [
	'id',
	'tag',
	'desc'
    ];
}