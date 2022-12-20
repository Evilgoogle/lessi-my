<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

class AuftragKundenhinweiseKommentars extends Model
{
    public $timestamps	 = false;
    protected $primaryKey	 = 'id';
    protected $table	 = 'checker_auftrag_kundenhinweise_kommentars';
}
