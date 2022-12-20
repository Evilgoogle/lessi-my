<?php
namespace App\Support\LeadAuto\Data\Activity;

use App\Support\LeadAuto\Data\LeadCreatorFahrzeug;
/**
 * Description of ActivityHws
 *
 * @author joker
 */
class ActivityHws extends ActivityBasic
{

    public function run()
    {
        parent::run();

        $this->setFahrzeug();
    }

    protected function create()
    {
        parent::create();

        $text = '<b>'.$this->target->form.'</b><br/>';
        foreach (json_decode($this->target->data) as $key => $val) {
            $text .= $key . ' : ' . $val . '<br/>';
        }

        $this->activity->date_time = (new \DateTime($this->target->date))->format('Y-m-d H:i:s');
        $this->activity->direction = \App\Models\LeadAuto\Activity::DIRECTION_IN;
        $this->activity->status = 0;
        $this->activity->note = $text ?? null;
        $this->activity->save();
    }

    private function setFahrzeug()
    {
        if(isset($this->activity->note)) {
            if(preg_match('#Auto\spage#ui', $this->activity->note)) {
                preg_match('#id\s\:\s(.+?)\s*\<br#ui', $this->activity->note, $math);

                $set = new LeadCreatorFahrzeug($this->lead, $math[1]);
                $set->run();
            }
        }
    }
}
