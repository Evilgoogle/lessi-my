<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\LeasBack\Junge;

use Illuminate\Database\Capsule\Manager as DB;
use Firebird\Eloquent\Model;

/**
 * Description of Fahrzeug
 *
 * @author rijen
 */
class Fahrzeug extends Model
{
    protected $connection = 'junge';
    protected $table = 'kosyfa_leasing';
    public $timestamps = false;
    
    public static function search($fin) 
    {
        return DB::connection('junge')->table('kosyfa_leasing')
        ->where('fin', 'LIKE', '%'.$fin.'%')
        ->limit(5)
        ->get();
    }
}
