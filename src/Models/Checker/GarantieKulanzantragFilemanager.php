<?php

namespace App\Models\Checker;

use Illuminate\Database\Eloquent\Model;

/**
 * Description of GarantieKulanzantragFilemanager
 *
 * @author joker
 */
class GarantieKulanzantragFilemanager extends Model 
{
    public $incrementing = true;
    protected $primaryKey = 'id';
    protected $table = 'checker_garantie_kulanzantrag_filemanager';
    
    public static function paginator($where = null) {
        $query = Filemanager::select('id');
        if ($where !== null) {
            $query->where($where);
        }

        return $query->count();
    }
}