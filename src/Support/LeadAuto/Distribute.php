<?php
namespace App\Support\LeadAuto;

use App\Models\Ats\Call;
use App\Models\LeadAuto\Lead;
use App\Models\LeadAuto\User2Lead;
use App\Support\LeadAuto\Libs\Timer;
use App\Models\UserManager\User;
use App\Models\LeadAuto\User as LeadUser;
use App\Models\MACS\Mitarbeiter;
use App\Support\LeadAuto\Libs\Distrib;
use App\Support\LeadAuto\Libs\MailSender;

// Distribute - Забирает лид и отдает другому пользователю
class Distribute
{
    private $args;

    private $ready_users;

    public function __construct($args) {
        $this->args = $args;
    }
    
    public function run() 
    {
        $lead = Lead::where('timer_hash', $this->args['hash'])->where('id', $this->args['lead_id'])->first();

        if(isset($lead)) {
            Timer::destroy($this->args['lead_id'], $this->args['user_id'], $this->args['id']);

            if($lead->complete == 'not completed') {
                // Освобождение лида
                $lead->status = 'none';
                $lead->timer_end = null;
                $lead->timer_hash = null;
                $lead->save();

                User2Lead::where('id', $this->args['id'])->delete();

                // Отдаем лид
                $this->makeUsers();
                $this->testUsers((new \DateTime()), $this->args['user_id']);

                foreach($this->ready_users as $k=>$user) {
                    $isset = User2Lead::where('user_id', $user)->first();

                    if(isset($isset)) {
                        unset($this->ready_users[$k]);
                    }
                }

                $distrib = new Distrib([$lead], $this->ready_users);
                $distrib->run();
                $picked = $distrib->picked;

                foreach($picked as $p) {
                    $timer = new Timer();
                    
                    $give = new User2Lead();
                    $give->lead_id = $p['lead']->id;
                    $give->user_id = $p['user'];
                    $give->save();
                    
                    $lead = Lead::where('id', $p['lead']->id)->first();
                    $lead->timer_end = $timer->time;
                    $lead->timer_hash = md5(rand(1, 100).date('Y-m-d h:i:s'));
                    $lead->save();
                    
                    $timer->hash = $lead->timer_hash;
                    $timer->id = $give->id;
                    $timer->lead_id = $give->lead_id;
                    $timer->user_id = $give->user_id;
                    
                    $timer->create();

                    $this->mail();
                }
            }

        }
    }

    private function makeUsers() 
    {
        $users = User::where('macs_id', '<>', 0)->get();
        
        // добавляем пользователей
        foreach($users as $item) {
            $get = LeadUser::where('user_id', $item->id)->first();
            
            if(!isset($get)) {
                $set = new LeadUser();
                $set->user_id = $item->id;
                $set->save();
            }
        }

        // удаляем пользователей которых нет в users
        foreach(LeadUser::all() as $item) {
            if(!in_array($item->user_id, $users->pluck('id')->toArray())) {
                $item->delete();
            }
        }
    }
    
    private function testUsers($date, $user_id) 
    {   
        $date = $date->setTime(0,0,0,0);
        $users = LeadUser::all();
        
        foreach($users as $user) {
            $query = Mitarbeiter::select('MITARBEITER.MITARBEITERID')
                ->join('STEMPEL as S', 'S.MITARBEITERID', '=', 'MITARBEITER.MITARBEITERID')
                ->whereIn('S.STEMPELARTIDANFANG', [16, 17, 18, 19, 20, 21, 22, 23, 24, 25, 26, 27, 28, 29, 30, 31, 32, 33, 34, 35, 36, 37, 44, 45, 48, 49])
                ->where(function ($query) use ($date) {
                    $query->whereNull('MITARBEITER.AUSTRITTSDATUM')
                    ->orWhere('MITARBEITER.AUSTRITTSDATUM', '>=', $date);
                })
                ->where('S.ZEITANFANG', '<=', $date)
                ->where('S.ZEITENDE', '>=', $date)
                ->where('MITARBEITER.MITARBEITERID', $user->user_id)
                ->first();
                
            if(!isset($query)) {
                $this->ready_users[] = $user->user_id;
            }
        }

        $this->ready_users = [1, 116, 122, 124, 125, 126, 127, 128, 129]; // test
        foreach($this->ready_users as $k=>$u) {
            if($u == $user_id) {
                unset($this->ready_users[$k]);
            }
        }
    }

    private function mail() 
    {
        $send = new MailSender('transfer-lead', ['3devilmax@gmail.com']);
        $send->run();
    }
}
