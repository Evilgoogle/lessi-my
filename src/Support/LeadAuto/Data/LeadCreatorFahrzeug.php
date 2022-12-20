<?php
namespace App\Support\LeadAuto\Data;

use App\Models\LeadAuto\Fahrzeug;
use App\Models\LeadAuto\EastwoodFahrzeug;

/**
 * Description of LeadCreatorData
 *
 * @author joker
 */
class LeadCreatorFahrzeug 
{
    private $lead;
    private $target;
    
    public function __construct($lead, $target) 
    {
        $this->lead = $lead;
        $this->target = $target;
    }
    
    public function run()
    {
        $isset = EastwoodFahrzeug::where('fahrgestellnummer', 'LIKE', '%'.$this->target)->first();
        
        if(isset($isset)) {
            $set = new Fahrzeug();
            $set->lead_id = $this->lead->id;
            $set->fahrgestellnummer = $isset->fahrgestellnummer;
            $set->fahrname = $isset->modelltext;
            $set->price = $isset->listenneupreis;
            $set->farbeaussen = $isset->farbeaussen;
            $set->farbeinnen = $isset->farbeinnen;
            $set->save();
        }
    }
}
