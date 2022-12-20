<?php
namespace App\Support\LeadAuto\Data;

use App\Models\LeadAuto\Data;

/**
 * Description of LeadCreatorData
 *
 * @author joker
 */
class LeadCreatorData 
{
    private $lead;
    private $macs;
    private $name;

    public function __construct($lead, $macs_data, $name = null)
    {
        $this->lead = $lead;
        $this->macs = $macs_data;
        
        $this->name = $name;
    }
    
    public function run() 
    {
        $set = Data::where('lead_id', $this->lead->id)->first();
        if(!isset($set)) {
           $set = new Data();
           $set->lead_id = $this->lead->id;
        }
        
        $set->name = isset($this->name) ? $this->name : null;
        $set->kunde_id = isset($this->macs) ? $this->macs->kunde_id : null;
        $set->kunde_nr = isset($this->macs) ? $this->macs->kunde_nr : null;
        $set->adresse_id = isset($this->macs) ? $this->macs->adresse_id : null;
        $set->interessent_id = isset($this->macs) ? $this->macs->interessent_id : null;
        $set->edealer_id = isset($this->macs) ? $this->macs->edealer_id : null;
        $set->save();
    }
}
