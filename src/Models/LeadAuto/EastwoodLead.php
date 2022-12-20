<?php

namespace App\Models\LeadAuto;

use Illuminate\Database\Eloquent\Model;

class EastwoodLead extends Model
{
    protected $connection = 'mobile';
    public $timestamps	 = false;
    protected $table	 = 'leads';
}
