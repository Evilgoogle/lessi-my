<?php
namespace App\Support\LeadAuto\Data\Activity;

use App\Models\LeadAuto\Activity;

/**
 * Description of LeadCreatorActivity
 *
 * @author EvilGoogle
 */
class ActivityBasic
{
    protected $lead;
    protected $target;
    protected $activity;
    protected $type;
    protected $source;
    protected $filial;

    public function __construct($lead, $target, $type, $source = null, $filial = null)
    {
        $this->lead = $lead;
        $this->target = $target;

        $this->type = $type;
        $this->source = $source;
        $this->filial = $filial;
    }

    public function run()
    {
        if($this->check()) {

            $this->create();
            //$this->recalc();
        }
    }

    protected function check()
    {
        $isset = $this->lead->activity()->where('type', $this->type)->where('external_id', $this->target->id)->first();

        if(!isset($isset)) {
            return true;
        } else {
            return false;
        }
    }

    protected function create()
    {
        $this->activity = new Activity();
        $this->activity->external_id = $this->target->id;
        $this->activity->lead_id = $this->lead->id;
        $this->activity->type = $this->type;
        $this->activity->source = $this->source;
        $this->activity->filial = $this->filial;
        $this->activity->save();
    }

    protected function recalc()
    {
        if ($this->lead->answer_status != 1) {
            if ($this->activity->direction == 1) {
                if (!$this->lead->prev_mark) {
                    $this->lead->answer_time	 = 0;
                    $this->activity->answer_time	 = 0;
                    $this->lead->prev_mark		 = 0;
                    $this->lead->answer_status	 = 1;
                } else {
                    if (strtotime($this->activity->date_time) > strtotime($this->lead->prev_mark)) {
                        $this->activity->answer_time	 = strtotime($this->activity->date_time) - strtotime($this->lead->prev_mark);
                        $this->lead->answer_time	 = strtotime($this->activity->date_time) - strtotime($this->lead->prev_mark);
                        $this->lead->prev_mark		 = 0;
                        $this->lead->answer_status	 = 1;
                    }
                }
            } else {
                if (!$this->lead->prev_mark) {
                    $this->lead->prev_mark		 = $this->activity->date_time;
                    $this->lead->answer_time	 = 0;
                    $this->activity->answer_time	 = -1;
                }
            }
        } else {
            if ($this->activity->direction == 0) {
                $prev_act = $this->lead->activity()->orderBy('date_time', 'desc')->first();
                if ($prev_act->id == $this->activity->id || strtotime($this->activity->date_time) > strtotime($this->lead->prev_mark)) {
                    $this->activity->answer_time	 = -1;
                    $this->lead->answer_status	 = 0;
                    $this->lead->answer_time	 = 0;
                    $this->lead->prev_mark		 = $this->activity->date_time;
                }
            }
        }
        if (!$this->lead->source && $this->activity->source) {
            $this->lead->source = $this->activity->source;
        }
        $this->lead->save();
        $this->activity->save();
    }
}
