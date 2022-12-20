<?php

namespace App\Models\LeasBack;

use Illuminate\Database\Eloquent\Model;

class Filemanager extends Model
{
    protected $connection = 'info';
    public $incrementing = true;
    protected $primaryKey = 'id';
    protected $table = 'leasing_datas_filemanagers';
    
    public static function paginator($where = null) {
        $query = Filemanager::select('id');
        if ($where !== null) {
            $query->where($where);
        }

        return $query->count();
    }
}