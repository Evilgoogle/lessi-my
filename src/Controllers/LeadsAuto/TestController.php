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

use App\Models\LeadAuto\LeadTest;
use App\Models\LeadAuto\ActivityTest;
use App\Models\LeadAuto\ContactTest;
use App\Models\LeadAuto\FahrzeugTest;
use App\Models\LeadAuto\FollowUpTest;
use Illuminate\Database\Eloquent\Model;

/**
 * Description of MyLeadController
 *
 * @author joker
 */
class TestController extends Controller
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

    public function add($request, $response)
    {
        $this->view->render($response, 'lead_autos/tests/add.twig');
    }

    /*public function insert($request, $response)
    {
        $lead = new LeadTest();
        $lead->save();

        $activity = new ActivityTest();
        $activity->lead_id = $lead->id;
        $activity->date_time = date('y-m-d');
        $activity->direction = 1;
        $activity->from = 'test_from';
        $activity->to = 'test_to';
        $activity->status = 1;
        $activity->type = 'mail';
        $activity->source = 1;
        $activity->save();

        foreach ($request->getParam('phone') as $item) {
            $contact = new ContactTest();
            $contact->lead_id = $lead->id;
            $contact->type = 'phone';
            $contact->value = $item;
            $contact->save();
        }

        foreach ($request->getParam('email') as $item) {
            $contact = new ContactTest();
            $contact->lead_id = $lead->id;
            $contact->type = 'email';
            $contact->value = $item;
            $contact->save();
        }

        foreach ($request->getParam('fahrzeug') as $item) {
            $contact = new FahrzeugTest();
            $contact->lead_id = $lead->id;
            $contact->fahrname = $item['fahrname'];
            $contact->fahrgestellnummer = $item['fahrgestellnummer'];
            $contact->save();
        }

        dd('lead added');
    }

    public function hot($request, $response)
    {
        $hot = LeadTest::whereHas('activity', function($q) {
            return $q->where('source', '!=', 0);
        })
            ->where('complete', 'not completed')
            ->where('status', 'none')
            ->orderBy('id', 'ASC')
            ->first();

        $this->view->render($response, 'lead_autos/tests/open.twig', [
            'hot' => $hot
        ]);
    }

    public function classification($request, $response)
    {
        $set = LeadTest::find($request->getParam('lead_id'));
        if(!isset($set)) {
            return $response->withStatus(404);
        }

        $set->status = $request->getParam('pick');
        $set->save();

        if($set->status == 'lead') {
            return $response->withRedirect('/leads-auto/test/show/'.$set->id, 301);
        } else {
            return $response->withRedirect('/', 301);
        }
    }

    public function my($request, $response)
    {
        $sources = Source::whereIn('id', [1,2,6,10])->get();

        $leads = LeadTest::whereHas('activity', function($q) {
            return $q->where('source', '!=', 0);
        })
            ->where('status', 'lead')
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

        foreach ($leads as $kl=>$lead) {
            foreach ($lead['user2lead'] as $k=>$user2lead) {
                if($k == 'user_id') {
                    $leads[$kl]['user2lead'][$k] = \App\Models\UserManager\User::find($user2lead)->name;
                }
            }
        }

        $this->view->render($response, 'lead_autos/tests/index.twig', [
            'leads' => $leads,
            'sources' => $sources,
            'counts' => $counts,
            'type' => 'My'
        ]);
    }

    public function show($request, $response, $args)
    {
        $date	 = new \DateTime('now');
        $date->modify('-1 month');

        $id = $args['id'];
        $lead = \App\Models\LeadAuto\LeadTest::where('status', 'lead')->where('id', $id)->first();

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
                $act			 = \App\Models\LeadAuto\ActivityTest::find($actvity['id']);
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
        $follow_ups = FollowUpTest::where('lead_id', $id)->orderBy('created_at', 'DESC')->get();

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

        $this->view->render($response, 'lead_autos/tests/show.twig', [
            'lead' => $lead,
            'mails' => $mails,
            'user' => $user,
            'emails' => $emails,
            'follow_up' => $follow_up,
            'follow_ups' => $follow_ups,
            'isset_contact' => $isset_contact
        ]);
    }

    public function called($request, $response)
    {
        if (!$this->Auth->user())
            return $response->withRedirect($this->router->pathFor('errors.403'));

        // Call to
        $to = $request->getParam('to');
        $phone = str_replace('-', '', $request->getParam('agent'));

        if ($to && $phone) {
            $inopla = new \App\Support\Inopla(false);
            $result = $inopla->Click2Call($phone, $to, 'LeadAuto');
        }

        $calls = file_get_contents('https://api.inopla.de/v1000/json/1206/804e3a70dabac48e45bb3fb68a19a81b/Live/Calls');
        $calls = json_decode($calls, true);
        $callers = [];
        if (isset($calls['response']['data'])) {
            foreach ($calls['response']['data'] as $call) {
                $callers[] = $call['caller'];
            }
        }
        //dd($calls, $callers);

        // Status
        //$set = LeadTest::find($request->getParam('lead_id'));
        //$set->called = true;
        //$set->save();

        //return $response->withJson([$calls, $callers]);
    }

    public function written($request, $response)
    {
        $from = $request->getParam('email_from');
        $to = $request->getParam('email_to');

        $title = $request->getParam('email_title');
        $message = $request->getParam('email_text');

        $send = new MailSender('mail', [$to], $from, $title, $message);
        $send->run();

        $set = LeadTest::find($request->getParam('lead_id'));
        $set->written = true;
        $set->save();

        return $response->withJson($set);
    }

    public function call_history($request, $response)
    {
        $calls = file_get_contents('https://api.inopla.de/v1000/json/1206/804e3a70dabac48e45bb3fb68a19a81b/Live/Calls');
        $calls = json_decode($calls, true);
        $callers = [];
        if (isset($calls['response']['data'])) {
            foreach ($calls['response']['data'] as $call) {
                $callers[] = $call['caller'];
            }
        }
        return $response->withJson($callers);
        // test

        $lead = LeadTest::find($request->getParam('lead_id'));

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
        foreach(ContactTest::where('lead_id', $request->getParam('lead_id'))->get() as $item) {
            if($item->type == 'phone') {
                $isset['phone'] = true;
            } elseif($item->type == 'email') {
                $isset['email'] = true;
            }
        }
        $isset = array_unique($isset);

        $lead = LeadTest::find($request->getParam('lead_id'));
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

        $set = new FollowUpTest();
        $set->lead_id = $lead_id;
        $set->date = $date;
        $set->save();

        return $response->withJson('ok');
    }*/

    public function insert($request, $response)
    {
        $lead = new LeadTest();
        $lead->desc = $request->getParam('desc');
        $lead->save();

        foreach ($request->getParam('phone') as $item) {
            $contact = new ContactTest();
            $contact->lead_id = $lead->id;
            $contact->type = 'phone';
            $contact->value = $item;
            $contact->save();
        }

        foreach ($request->getParam('email') as $item) {
            $contact = new ContactTest();
            $contact->lead_id = $lead->id;
            $contact->type = 'email';
            $contact->value = $item;
            $contact->save();
        }

        foreach ($request->getParam('fahrzeug') as $item) {
            $contact = new FahrzeugTest();
            $contact->lead_id = $lead->id;
            $contact->fahrname = $item['fahrname'];
            $contact->fahrgestellnummer = $item['fahrgestellnummer'];
            $contact->save();
        }

        foreach ($request->getParam('verlauf') as $item) {
            $contact = new ActivityTest();
            $contact->lead_id = $lead->id;
            $contact->datum = $item['datum'];
            $contact->type = $item['type'];
            $contact->mitarbeiter = $item['mitarbeiter'];
            $contact->save();
        }

        return $response->withRedirect('/leads-auto/test/hot');
    }

    public function hot($request, $response)
    {
        $hot = LeadTest::where('complete', 'not completed')
            ->where('status', 'none')
            ->orderBy('created_at', 'DESC')
            ->first();

        $time_future = (new \DateTime(date('Y-m-d H:i:s')))->add(\DateInterval::createFromDateString('+10 minute'));
        $time_now = new \DateTime(date('Y-m-d H:i:s'));
        $time = $time_future->diff($time_now);

        if($hot->complete != 'completed') {
            $time = ['h' => $time->h, 'i' => $time->i, 's' => $time->s];
        } else {
            $time = 0;
        }

        $this->view->render($response, 'lead_autos/tests/open.twig', [
            'hot' => $hot,
            'time' => $time
        ]);
    }

    public function classification($request, $response)
    {
        $set = LeadTest::find($request->getParam('lead_id'));
        if(!isset($set)) {
            return $response->withStatus(404);
        }

        $set->status = $request->getParam('pick');
        $set->save();

        if($set->status == 'lead') {
            return $response->withRedirect('/leads-auto/test/show/'.$set->id, 301);
        } else {
            return $response->withRedirect('/', 301);
        }
    }

    public function my($request, $response)
    {
        $sources = Source::whereIn('id', [1,2,6,10])->get();
        $leads = LeadTest::where('status', 'lead')->get();

        $this->view->render($response, 'lead_autos/tests/index.twig', [
            'leads' => $leads,
            'sources' => $sources,
            'type' => 'My'
        ]);
    }

    public function show($request, $response, $args)
    {
        $id = $args['id'];
        $lead = \App\Models\LeadAuto\LeadTest::where('status', 'lead')->where('id', $id)->first();

        if(!isset($lead)) {
            return $response->withRedirect($this->router->pathFor('test_leads_auto.my'));
        }

        $user = $this->Auth->user();

        // Follow up
        $follow_up = [
            'tommorow' => (new \DateTime())->modify('+1 day')->format('d.m.Y'),
            'day3' => (new \DateTime())->modify('+3 day')->format('d.m.Y'),
            'week' => (new \DateTime())->modify('+7 day')->format('d.m.Y')
        ];
        $follow_ups = FollowUpTest::where('lead_id', $id)->orderBy('created_at', 'DESC')->get();

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

        // Time
        $time_future = (new \DateTime(date('Y-m-d H:i:s')))->add(\DateInterval::createFromDateString('+10 minute'));
        $time_now = new \DateTime(date('Y-m-d H:i:s'));
        $time = $time_future->diff($time_now);

        if($lead->complete != 'completed') {
            $time = ['h' => $time->h, 'i' => $time->i, 's' => $time->s];
        } else {
            $time = 0;
        }

        $this->view->render($response, 'lead_autos/tests/show.twig', [
            'lead' => $lead,
            'user' => $user,
            'follow_up' => $follow_up,
            'follow_ups' => $follow_ups,
            'isset_contact' => $isset_contact,
            'time' => $time
        ]);
    }

    public function called($request, $response)
    {
        if (!$this->Auth->user())
            return $response->withRedirect($this->router->pathFor('errors.403'));

        $to = $request->getParam('to');
        $phone = str_replace('-', '', $request->getParam('agent'));

        // Звонок
        if ($to && $phone) {
            $inopla = new \App\Support\Inopla(false);
            $inopla->Click2Call($phone, $to, 'LeadAuto');
        }

        // Проверка звонка
        $calls = file_get_contents('https://api.inopla.de/v1000/json/1206/804e3a70dabac48e45bb3fb68a19a81b/Live/Calls');
        $calls = json_decode($calls, true);

        $result = $this->testing_call($calls, $phone, $to);

        return $response->withJson($result);
    }

    public function called_wait($request, $response)
    {
        $to = $request->getParam('to');
        $phone = str_replace('-', '', $request->getParam('agent'));

        // Проверка звонка
        $calls = file_get_contents('https://api.inopla.de/v1000/json/1206/804e3a70dabac48e45bb3fb68a19a81b/Live/Calls');
        $calls = json_decode($calls, true);

        // Status
        if($this->testing_call($calls, $phone, $to) == 'ok') {
            $set = LeadTest::find($request->getParam('lead_id'));
            $set->called = true;
            $set->save();

            return $response->withJson('ok');
        } else {
            return $response->withJson('no');
        }
    }

    private function testing_call($calls, $phone, $to)
    {
        $launched = [
            'agent' => false,
            'to' => false
        ];
        if(count($calls['response']['data']) > 0) {
            foreach ($calls['response']['data'] as $data) {
                $agent = preg_replace('#^0#ui', '0049', $phone);
                if($data['caller'] == $agent) {
                    if($data['service'] == 'Click2Call-LeadAuto') {
                        $launched['agent'] = true;
                    }
                }

                $caller = preg_replace('#[0\+]+#ui', '', $data['caller']);
                $to = preg_replace('#[0\+]+#ui', '', $to);
                if($caller == $to) {
                    $launched['to'] = true;
                }
            }
        }

        if($launched['agent'] && $launched['to']) {
            return 'ok';
        } else {
            return 'no';
        }
    }

    public function written($request, $response)
    {
        $from = $request->getParam('email_from');
        $to = $request->getParam('email_to');

        $title = $request->getParam('email_title');
        $message = $request->getParam('email_text');

        $send = new MailSender('mail', [$to], $from, $title, $message);
        $send->run();

        $set = LeadTest::find($request->getParam('lead_id'));
        $set->written = true;
        $set->save();

        return $response->withJson($set);
    }

    public function call_history($request, $response)
    {
        $lead = LeadTest::find($request->getParam('lead_id'));

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
        foreach(ContactTest::where('lead_id', $request->getParam('lead_id'))->get() as $item) {
            if($item->type == 'phone') {
                $isset['phone'] = true;
            } elseif($item->type == 'email') {
                $isset['email'] = true;
            }
        }
        $isset = array_unique($isset);

        $lead = LeadTest::find($request->getParam('lead_id'));
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

        $set = new FollowUpTest();
        $set->lead_id = $lead_id;
        $set->date = $date;
        $set->save();

        return $response->withJson('ok');
    }
}
