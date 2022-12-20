<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

class StepStatus extends Model
{
    public $timestamps    = false;
    public $incrementing  = true;
    protected $primaryKey = 'id';
    protected $table      = 'checker_step_status';
    protected $fillable   = [ 'auftrag_id', 'step', 'status',];
    
    public function children()
    {
	return $this->hasMany(StepChildrenStatus::class, 'step_id', 'id');
    }
}
