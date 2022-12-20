<?php

/**
 * This file is part of Lessi project.
 * © byvlad, 2022
 */

namespace App\Models\LeasBack;

use Firebird\Eloquent\Model;

class LeasBack extends Model
{
    protected $connection	   = 'info';
    protected $table	        = 'leasing_datas';
    public $timestamps	      = false;

    protected $fillable = [
        'name', 'email', 'phone', 'interested', 'mark', 'dt', 'leasing_from_site'
    ];
}
