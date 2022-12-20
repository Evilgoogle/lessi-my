<?php
namespace App\Support\LeadAuto\Sources;

use App\Support\LeadAuto\Data\LeadCreator;
use App\Support\LeadAuto\Data\Activity\ActivityHws;

class CreateFromHWS
{
    public $lead = null;
    private $data;
    private $item;
    private $source = 10;

    public function run($item)
    {
        $this->data = json_decode($item->data);
        $this->item = $item;

        $this->setLead();
        $this->setContacts();
        $this->setActivity($item);
    }

    private function setLead()
    {
        if (isset($this->data->phone)) {
            $set = new LeadCreator($this->data->phone, 'phone');
            $set->setName($this->data->name ?? '');
            $set->run();
            $this->lead	= $set->lead;
        }

        if (!$this->lead && isset($this->data->email)) {
            $set = new LeadCreator($this->data->email, 'email');
            $set->setName($this->data->name ?? '');
            $set->run();
            $this->lead	= $set->lead;
        }
    }

    private function setActivity($item)
    {
        $get = new ActivityHws($this->lead, $item, 'hws', $this->source);
        $get->run();
    }

    private function setContacts()
    {
        if (isset($this->data->phone)) {
            $this->lead->contacts()->firstOrCreate(['type' => 'phone', 'value' => $this->data->phone]);
        }

        if (isset($this->data->email)) {
            $this->lead->contacts()->firstOrCreate(['type' => 'email', 'value' => $this->data->email]);
        }

        $this->lead->load('contacts');
    }
}
