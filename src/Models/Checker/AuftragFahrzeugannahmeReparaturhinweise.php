<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

class AuftragFahrzeugannahmeReparaturhinweise extends Model
{
    public $timestamps	 = false;
    protected $primaryKey	 = 'id';
    protected $table	 = 'checker_auftrag_fahrzeugannahme_reparaturhinweise';
}
