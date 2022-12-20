<?php
namespace App\Support\LeadAuto\Data\Activity;

/**
 * Description of ActivityMacs
 *
 * @author joker
 */
class ActivityMacs extends ActivityBasic 
{
    protected function create() 
    {
        parent::create();
        
        $note = 'Dealer status: ' . $this->target->DEALERSTATUS . '; Lead type: ' . $this->target->LEADTYPE . '; Status: ' . $this->target->STATUS . '; ' . "\n";
        $dt = new \DateTime($this->target->STARTDATE);
        
        $this->activity->date_time = $dt->format('Y-m-d H:i:s');
        $this->activity->direction = 0;
        $this->activity->status = 0;
        $this->activity->note = $note . $this->target->OPPORTUNITYNOTES;   
        $this->activity->save();
    }
}
