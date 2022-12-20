<?php
namespace App\Models\LeadAuto;

use Illuminate\Database\Eloquent\Model;

class Lead extends Model
{
    protected $table = 'lead_autos';
    
    public function contacts ()
    {
        return $this->hasMany(Contact::class, 'lead_id');
    }
    
    public function activity()
    {
	return $this->hasMany(Activity::class, 'lead_id');
    }
    
    public function fahrzeug()
    {
        return $this->hasMany(Fahrzeug::class, 'lead_id');
    }
    
    public function data()
    {
        return $this->hasOne(Data::class, 'lead_id');
    }
    
    public function user2lead()
    {
        return $this->hasOne(User2Lead::class, 'lead_id');
    }
}
