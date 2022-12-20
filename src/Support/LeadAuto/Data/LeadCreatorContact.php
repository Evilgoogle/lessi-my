<?php
namespace App\Support\LeadAuto\Data;

use App\Support\LeadAuto\Libs\LeadDataSeeker;
use App\Models\LeadAuto\Contact;
use App\Models\MACS\Kommunication;

class LeadCreatorContact
{
    public function __construct($lead, $contact, $type)
    {
        $this->lead = $lead;

        $this->contact = $contact;
        $this->type = $type;
    }

    public function insert()
    {
        $set = new Contact();
        $set->lead_id = $this->lead->id;
        $set->type = $this->type;
        $set->value = $this->contact;
        $set->save();
    }

    public function attach()
    {
        $contacts_arr = LeadDataSeeker::getContactsInMacs($this->lead->adresse_id);
        $haved_contacts = $this->lead->contacts->toArray();

        foreach ($contacts_arr as $new_contact) {
            if (!in_array($new_contact['value'], array_column($haved_contacts, 'value'))) {
                if ($new_contact['value']) {

                    $this->lead->contacts()->create($new_contact);
                }
            }
        }
    }
}