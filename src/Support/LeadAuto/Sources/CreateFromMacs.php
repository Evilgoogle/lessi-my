<?php
namespace App\Support\LeadAuto\Sources;

use App\Support\LeadAuto\Data\LeadCreator;
use App\Support\LeadAuto\Data\Activity\ActivityMacs;

/**
 * Description of ActivityCreateHWS
 *
 * @author evilgoogle
 */
class CreateFromMacs
{
    private $lead = null;
    private $source = 2;

    public function run ($item)
    {
        if ($this->find($item)) {
            
            $this->setActivity($item);
        }
    }
    
    private function setActivity($item) 
    {
        $get = new ActivityMacs($this->lead->lead, $item, 'macs', $this->source);
        $get->run();
    }
    
    private function find ($item)
    {
        foreach ($item->toArray() as $k => $v) {
            if (in_array($k, [ 'HOMEPHONE', 'CELLULARPHONE' ]) && !empty($v)) {
                $set = new LeadCreator($v, 'phone');
                $set->run();
                
                $this->lead = $set;
            }
            if (in_array($k, [ 'PERSONALEMAIL', 'WORKEMAIL' ]) && !empty($v)) {
                $set = new LeadCreator($v, 'email');
                $set->run();
                
                $this->lead = $set;
            }
        }

        return $this->lead;
    }
}
