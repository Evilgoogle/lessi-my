<?php
namespace App\Controllers\LeadsAuto;

use App\Controllers\Controller;
use App\Models\LeadAuto\Lead;
use App\Models\LeadAuto\Activity;
use App\Models\Leads\Source;
use App\Models\LeadAuto\User2Lead;
use App\Models\LeadAuto\Contact;
use App\Support\LeadAuto\Libs\MailSender;
use App\Support\LeadAuto\ProcessDistribution;
use App\Models\Ats\Call;
use App\Models\LeadAuto\FollowUp;
/**
 * Description of MyLeadController
 *
 * @author joker
 */
class LeadController extends Controller
{
    private function dateConvert($date)
    {
        if(!empty($date)) {
            preg_match('#([0-9]*)\.([0-9]*)\.([0-9]*)#ui', $date, $match);

            return $match[3].'-'.$match[2].'-'.$match[1].' 00:00:00';
        } else {
            return null;
        }
    }

    public function my($request, $response)
    {
        $sources = Source::whereIn('id', [1,2,6,10])->get();

        $user_leads = User2Lead::where('user_id', $this->Auth->user()->id)->get()->pluck('lead_id')->toArray();

        $leads = Lead::whereHas('activity', function($q) {
            return $q->where('source', '!=', 0);
        })
            ->whereIn('id', $user_leads)
            ->where('status', 'lead')
            ->get();

        $leads->load('activity');
        $leads->load('contacts');
        $leads->load('user2lead');

        $leads = $leads->toArray();

        $counts = ['total' => count($leads)];
        foreach ($leads as &$row) {
            foreach ($row['activity'] as $item) {
                if ($item['source'] != 0) {
                    $row['src'] = $item['source'];
                }
            }
            if (isset($counts[$row['src']])) {
                $counts[$row['src']]++;
            } else {
                $counts[$row['src']] = 1;
            }
        }

        foreach ($leads as $kl=>$lead) {
            foreach ($lead['user2lead'] as $k=>$user2lead) {
                if($k == 'user_id') {
                    $leads[$kl]['user2lead'][$k] = \App\Models\UserManager\User::find($user2lead)->name;
                }
            }
        }

        $this->view->render($response, 'lead_autos/index.twig', [
            'leads' => $leads,
            'sources' => $sources,
            'counts' => $counts,
            'type' => 'My'
        ]);
    }

    public function all($request, $response)
    {
        $sources = Source::whereIn('id', [1,2,6,10])->get();

        $leads = Lead::whereHas('activity', function($q) {
            return $q->where('source', '!=', 0);
        })
            ->where('status', 'lead')
            ->where('complete', 'completed')
            ->get();

        $leads->load('activity');
        $leads->load('contacts');

        $leads = $leads->toArray();

        $counts = ['total' => count($leads)];
        foreach ($leads as &$row) {
            foreach ($row['activity'] as $item) {
                if ($item['source'] != 0) {
                    $row['src'] = $item['source'];
                }
            }
            if (isset($counts[$row['src']])) {
                $counts[$row['src']]++;
            } else {
                $counts[$row['src']] = 1;
            }
        }

        $this->view->render($response, 'lead_autos/index.twig', [
            'leads' => $leads,
            'sources' => $sources,
            'counts' => $counts,
            'type' => 'All'
        ]);
    }

    public function show($request, $response, $args)
    {
        $date	 = new \DateTime('now');
        $date->modify('-1 month');

        $id = $args['id'];
        $lead = \App\Models\LeadAuto\Lead::where('status', 'lead')->where('id', $id)->first();
        $sources = \App\Models\Leads\Source::all();

        if(!isset($lead)) {
            return $response->withRedirect($this->router->pathFor('leads_auto.my'));
        }

        $lead = $lead->load([
            'activity' => function($query) use ($date) {
                return $query->orderBy('date_time', 'DESC');
            },
            'contacts',
            'fahrzeug'
        ]);

        $emailAdresse = $lead->contacts->filter(function($item) {
            if ($item->type == 'email')
                return true;
        });

        $mails = collect([]);
        foreach ($emailAdresse as $adress) {
            $mails_tmp = \App\Models\Mail\Mail::where('from', $adress->value)->get();

            foreach ($mails_tmp as $item) {
                $mails->prepend($item);
            }
        }

        $lead = $lead->toArray();

        foreach ($lead['activity'] as &$actvity) {
            if ($actvity['type'] == 'call') {
                $act			 = \App\Models\LeadAuto\Activity::find($actvity['id']);
                $actvity['external']	 = $act->external->load('abonents')->toArray();
            }
        }

        $user = $this->Auth->user();

        $emails = [];
        foreach($lead['contacts'] as $contact) {
            if($contact['type'] == 'email') {
                $emails[] = $contact['value'];
            }
        }

        // Follow up
        $follow_up = [
            'tommorow' => (new \DateTime())->modify('+1 day')->format('d.m.Y'),
            'day3' => (new \DateTime())->modify('+3 day')->format('d.m.Y'),
            'week' => (new \DateTime())->modify('+7 day')->format('d.m.Y')
        ];
        $follow_ups = FollowUp::where('lead_id', $id)->orderBy('created_at', 'DESC')->get();

        // Контакты чтоб показать обработан или нет
        $isset_contact = [];
        foreach($lead['contacts'] as $contact) {
            if($contact['type'] == 'phone') {
                $isset_contact[] = 'phone';
            }
            if($contact['type'] == 'email') {
                $isset_contact[] = 'email';
            }
        }
        $isset_contact = collect($isset_contact)->unique();
        //

        // Timer
        $timer = exec('SCHTASKS/Query /V /FO "CSV" /TN "\Lessi LeadsAuto\timer_'.$lead['id'].'_'.$this->Auth->user()->id.'_'.User2Lead::where('lead_id', $lead['id'])->first()->id.'"');
        if(!empty($timer)) {
            $get_time = explode(',', $timer)[2];
            preg_match('#([0-9]+)\/([0-9]+)\/([0-9]+)\s*(.*)\"#ui', $get_time, $match);

            $time_future = new \DateTime(date('Y-m-d H:i:s', strtotime($match[2].'-'.$match[1].'-'.$match[3].' '.$match[4])));
            $time_now = new \DateTime(date('Y-m-d H:i:s'));
            $time = $time_future->diff($time_now);

            if($lead['complete'] != 'completed') {
                $time = ['h' => $time->h, 'i' => $time->i, 's' => $time->s];
            } else {
                $time = 0;
            }
        } else {
            if($lead['complete'] == 'completed') {
                $time = 0;
            } else {
                dd('ERROR: timer not found');
            }
        }

        $this->view->render($response, 'lead_autos/show.twig', [
            'lead' => $lead,
            'sources' => $sources,
            'mails' => $mails,
            'user' => $user,
            'emails' => $emails,
            'follow_up' => $follow_up,
            'isset_contact' => $isset_contact,
            'time' => $time,
            'follow_ups' => $follow_ups
        ]);
    }

    public function called($request, $response)
    {
        if (!$this->Auth->user())
            return $response->withRedirect($this->router->pathFor('errors.403'));

        // Call to
        /*$to = $request->getParam('to');
        $phone = str_replace('-', '', $request->getParam('agent'));

        if ($to && $phone) {
            $inopla = new \App\Support\Inopla(false);
            $result = $inopla->Click2Call($phone, $to, 'LeadAuto');
        }*/

        // Status
        $set = Lead::find($request->getParam('lead_id'));
        $set->called = true;
        $set->save();

        return $response->withJson($set);
    }

    public function written($request, $response)
    {
        $from = $request->getParam('email_from');
        $to = $request->getParam('email_to');

        $title = $request->getParam('email_title');
        $message = $request->getParam('email_text');

        $send = new MailSender('mail', [$to], $from, $title, $message);
        $send->run();

        $set = Lead::find($request->getParam('lead_id'));
        $set->written = true;
        $set->save();

        return $response->withJson($set);
    }

    public function email_type($request, $response)
    {
        $mails = [
            'Administrator',
        ];
        $user_name = $this->Auth->user()->name;

        $host = 'http://lessing.p7.de:82'; //$_SERVER['HTTP_ORIGIN'];

        if(in_array($user_name, $mails)) {
            $template = $this->view->fetch('lead_autos/mails/'.$user_name.'/index.twig', ['host' => $host]);

            return $response->withJson($template);
        }
    }

    public function call_history($request, $response)
    {
        $lead = \App\Models\LeadAuto\Lead::find($request->getParam('lead_id'));

        $begin = $lead->created_at;
        $end = (new \DateTime())->modify('+1 day')->format('Y-m-d h:i:s');

        $calls = Call::whereBetween('date_time', [$begin, $end])
            ->where(function ($query) use ($lead) {
                foreach($lead->contacts as $contact) {
                    if($contact->type == 'phone') {
                        $query->orWhere('caller', 'LIKE', '%'.substr($contact->value, 1));
                    }
                }
            })
            ->orderBy('date_time')
            ->get();

        return $response->withJson($this->view->fetch('lead_autos/templates/call_history.twig', ['calls' => $calls]));
    }

    public function complete($request, $response)
    {
        $isset = [];
        foreach(Contact::where('lead_id', $request->getParam('lead_id'))->get() as $item) {
            if($item->type == 'phone') {
                $isset['phone'] = true;
            } elseif($item->type == 'email') {
                $isset['email'] = true;
            }
        }
        $isset = array_unique($isset);

        $lead = Lead::find($request->getParam('lead_id'));
        if(isset($lead)) {
            if(array_key_exists('phone', $isset) && array_key_exists('email', $isset)) {
                if($lead->called && $lead->written) {
                    $lead->complete = 'completed';
                    $lead->save();
                }
            } elseif(array_key_exists('phone', $isset)) {
                if($lead->called) {
                    $lead->complete = 'completed';
                    $lead->save();
                }
            } elseif(array_key_exists('email', $isset)) {
                if($lead->written) {
                    $lead->complete = 'completed';
                    $lead->save();
                }
            }
        }

        return $response->withJson($lead);
    }

    public function follow_up_insert($request, $response)
    {
        $lead_id = $request->getParam('lead_id');
        $date = $this->dateConvert($request->getParam('date'));

        $set = new FollowUp();
        $set->lead_id = $lead_id;
        $set->date = $date;
        $set->save();

        return $response->withJson('ok');
    }
}
