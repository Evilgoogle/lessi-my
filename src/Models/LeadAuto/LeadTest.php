<?php
namespace App\Models\LeadAuto;

use Illuminate\Database\Eloquent\Model;

class LeadTest extends Model
{
    protected $table = 'lead_auto_test';
    
    public function contacts ()
    {
        return $this->hasMany(ContactTest::class, 'lead_id');
    }
    
    public function activity()
    {
	return $this->hasMany(ActivityTest::class, 'lead_id');
    }
    
    public function fahrzeug()
    {
        return $this->hasMany(FahrzeugTest::class, 'lead_id');
    }
}
