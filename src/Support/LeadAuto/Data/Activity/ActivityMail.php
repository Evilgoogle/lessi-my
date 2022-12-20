<?php
namespace App\Support\LeadAuto\Data\Activity;

/**
 * Description of ActivityMail
 *
 * @author Evilgoogle
 */
class ActivityMail extends ActivityBasic
{
    protected function create()
    {
        parent::create();
        
        $this->activity->date_time = $this->target->date_time;
        $this->activity->direction = $this->target->direction;
        $this->activity->from = $this->target->from;
        $this->activity->to = $this->target->to;
        $this->activity->status = $this->target->direction;
        $this->activity->note = isset($this->target->note) ? $this->target->note : null;
        $this->activity->save();
    }
}
