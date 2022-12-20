<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

class StepChildrenStatus extends Model
{
    public $timestamps    = false;
    public $incrementing  = true;
    protected $primaryKey = 'id';
    protected $table      = 'checker_step_children_status';
    protected $fillable   = [ 'step_id', 'step', 'status'];
}
