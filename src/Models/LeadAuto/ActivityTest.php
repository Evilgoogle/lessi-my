<?php
namespace App\Models\LeadAuto;

/**
 * Description of Activity
 *
 * @author joker
 */
use Illuminate\Database\Eloquent\Model;

class ActivityTest extends Model
{
    const DIRECTION_IN = 0;
    const DIRECTION_OUT = 1;
    
    public $timestamps  = false;
    protected $table    = 'lead_auto_test_activities';
    
    public function external ()
    {
        if ($this->type == 'call') {
            return $this->belongsTo(\App\Models\Ats\Call::class, 'external_id');
        } else {
            return null;
        }
    }
}