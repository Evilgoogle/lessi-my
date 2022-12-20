<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

class Filemanager extends Model
{
    public $incrementing = true;
    protected $primaryKey = 'id';
    protected $table = 'checker_filemanagers';
    
    public static function paginator($where = null) {
        $query = Filemanager::select('id');
        if ($where !== null) {
            $query->where($where);
        }

        return $query->count();
    }
}