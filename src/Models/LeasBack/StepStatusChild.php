<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\LeasBack;

use Firebird\Eloquent\Model;

/**
 * Description of StepStatusChild
 *
 * @author joker
 */
class StepStatusChild extends Model
{
    protected $connection = 'info';
    protected $table = 'leasing_datas_steps_children';
    public $timestamps = false;
    protected $primaryKey = 'id';
}
