<?php
namespace App\Support\LeadAuto\Data;

use App\Models\LeadAuto\Lead;
use App\Support\LeadAuto\Libs\LeadDataSeeker;

class LeadCreator
{
    public $lead;
    private $contact;
    private $contact_type;
    private $data_name = null;

    public function __construct($contact, $contact_type = 'phone')
    {
        $this->contact_type = $contact_type;
        $this->contact = ($this->contact_type == 'phone') ? preg_replace('/^(0049|[0]{1}|\+49)/', '', $contact) : $contact;
    }

    public function run()
    {
        $lead = LeadDataSeeker::findLeadByContact($this->contact);

        if(!isset($lead)) {
            $found_lead = $this->check_lead();

            if(!isset($found_lead)) {
                $this->insert();
            } else {
                $this->update($found_lead);
            }
        } else {
            $this->update($lead);
        }

        $this->attachActivity();
    }

    public function setName($name)
    {
        $this->data_name = $name;
    }

    private function check_lead()
    {
        $get = LeadDataSeeker::findLeadInMACS($this->contact, $this->contact_type);

        $lead = null;
        if (isset($get)) {
            $contacts = LeadDataSeeker::getContactsInMacs($get['ADRESSEID']);

            foreach ($contacts as $item) {
                $lead = LeadDataSeeker::findLeadByContact($item['value']);
            }
        }

        return $lead;
    }

    private function insert()
    {
        $set = new Lead();
        $set->save();

        $this->lead = $set;

        $this->setData($set);

        $this->setContact($set, 'insert');
    }

    private function update($lead)
    {
        $set = Lead::find($lead->id);

        $this->lead = $set;

        $this->setData($set);

        $this->setContact($set, 'update');
    }

    private function setData($lead)
    {
        $isset = LeadDataSeeker::findLeadInMACS($this->contact, $this->contact_type);

        $set = new LeadCreatorData($lead, $isset, $this->data_name);
        $set->run();
    }

    private function setContact($lead, $method)
    {
        $set = new LeadCreatorContact($lead, $this->contact, $this->contact_type);

        if($method == 'insert') {

            $set->insert();
        } elseif('update') {

            $set->attach();
        }
    }

    private function attachActivity()
    {
        foreach ($this->lead->contacts as $contact) {
            if (strlen($contact->value) < 5) {
                continue;
            }

            $leads = Lead::whereHas('contacts', function ($query) use ($contact) {
                $query->where('value', 'like', '%' . $contact->value)->orWhere('value', $contact->value);
            })
                ->where('id', '!=', $this->lead->id)
                ->get();


            foreach ($leads as $item) {
                $item->activity()->update(['lead_id' => $this->lead->id]);
            }
        }

        /*if ($lead->data->adresse_id) {
            $copies = Lead::where([['adresse_id', $lead->adresse_id], ['id', '!=', $lead->id]])->get();

            foreach ($copies as $copy) {
                    $copy->activity()->update(['lead_id' => $main->id]);
            }
        }*/
    }
}
