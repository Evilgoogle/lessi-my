<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

class AuftragDiagnosereparaturDokumentation extends Model
{
    public $timestamps	  = false;
    protected $primaryKey = 'id';
    protected $table	  = 'checker_auftrag_diagnosereparatur_dokumentation';
}