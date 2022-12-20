<?php

/*
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

namespace App\Models\LeasBack;

use Firebird\Eloquent\Model;

/**
 * Description of DirektannahmeTags
 *
 * @author joker
 */
class DirektannahmeTags extends Model
{
    protected $connection  = 'info';
    protected $table	   = 'leasing_datas_direktannahme_tags';
    public $timestamps	   = false;
    
    static function init($leasback_id, $type) {
        $tags = ['p31-1', 'p33-1', 'p34-1', 's1-1', 's2-1', 's3-1', 's4-1', 's5-1', 's6-1'];
        
        foreach($tags as $tag) {
            $set = new DirektannahmeTags();
            $set->leasback_id = $leasback_id;
            $set->tag = $tag;
            $set->type = $type;
            $set->save();
        }
    }
}
