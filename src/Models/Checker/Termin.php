<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

class Termin extends Model
{
    public $timestamps	 = false;
    public $incrementing  = false;
    protected $primaryKey  = 'id';
    protected $table = 'checker_termins';
}
