<?php
namespace App\Support\LeadAuto\Libs;

class Distrib
{
    public $picked = [];

    private $sets = [];
    private $leads;
    private $ready_users;

    public function __construct($leads, $ready_users)
    {
        $this->leads = $leads;
        $this->ready_users = $ready_users;
    }

    private function lead($user)
    {
        foreach($this->leads as $lead) {
            if(!in_array($lead->id, $this->sets)) {
                $this->sets[] = $lead->id;

                return [
                    'lead' => $lead,
                    'user' => $user
                ];
            }
        }

        return false;
    }

    public function run()
    {
        shuffle($this->ready_users);
        foreach($this->ready_users as $user) {
            $lead = $this->lead($user);

            if($lead) {
                $this->picked[] = $lead;
            }
        }
    }
}