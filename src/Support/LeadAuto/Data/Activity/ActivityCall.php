<?php
namespace App\Support\LeadAuto\Data\Activity;

/**
 * Description of ActivityCall
 *
 * @author Evilgoogle
 */
class ActivityCall extends ActivityBasic 
{
    public function run() 
    {
        if($this->check()) {
            if($this->type == 'call') {
                $this->removeCall();
            }
            
            $this->create();
            //$this->recalc();
        }
    }

    protected function removeCall ()
    {
        $get = $this->lead->activity()->where('type', 'call')->where('date_time', '>=', $this->target->date_time)->get();
        if ($get) {
            foreach ($get as $item) {
                $item->forceDelete();
            }
        }
    }
    
    protected function create() 
    {
        parent::create();
        
        $this->activity->date_time  = $this->target->date_time;
        $this->activity->direction  = $this->target->direction == 'inbound' ? 0 : 1;
        $this->activity->from       = $this->target->direction == 'inbound' ? $this->target->caller : $this->target->service;
        $this->activity->to         = $this->target->direction == 'inbound' ? $this->target->service . '-' . $this->target->ddi : $this->target->caller;
        $this->activity->status     = $this->target->successfully;
        $this->activity->save();
    }
}
