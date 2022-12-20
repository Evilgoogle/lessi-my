<?php
namespace App\Controllers\LeadsAuto;

use App\Controllers\Controller;
use App\Models\LeadAuto\Lead;
use App\Models\LeadAuto\Contact;
use App\Models\LeadAuto\Data;
use App\Models\LeadAuto\User2Lead;

class HotController extends Controller
{
    public function open($request, $response)
    {
        $hot = null;

        $get = User2Lead::where('user_id', $this->Auth->user()->id)->get();
        foreach($get as $item) {
            $hot = Lead::whereHas('activity', function($q) {
                return $q->where('source', '!=', 0);
            })
                ->where('id', $item->lead_id)
                ->where('complete', 'not completed')
                ->where('status', 'none')
                ->first();
        }

        if(isset($hot)) {
            // Timer
            $timer = exec('SCHTASKS/Query /V /FO "CSV" /TN "\Lessi LeadsAuto\timer_'.$hot->id.'_'.$this->Auth->user()->id.'_'.User2Lead::where('lead_id', $hot->id)->first()->id.'"');
            if(!empty($timer)) {
                $get_time = explode(',', $timer)[2];
                preg_match('#([0-9]+)\/([0-9]+)\/([0-9]+)\s*(.*)\"#ui', $get_time, $match);

                $time_future = new \DateTime(date('Y-m-d H:i:s', strtotime($match[2].'-'.$match[1].'-'.$match[3].' '.$match[4])));
                $time_now = new \DateTime(date('Y-m-d H:i:s'));
                $time = $time_future->diff($time_now);

                $time = ['h' => $time->h, 'i' => $time->i, 's' => $time->s];
            } else {
                if($lead['complete'] == 'completed') {
                    $time = 0;
                } else {
                    dd('ERROR: timer not found');
                }
            }
        }

        return $this->view->render($response, 'lead_autos/open.twig', [
            'hot' => $hot,
            'time' => $time
        ]);
    }

    public function classification($request, $response)
    {
        $set = Lead::find($request->getParam('lead_id'));
        if(!isset($set)) {
            return $response->withStatus(404);
        }

        $set->status = $request->getParam('pick');
        $set->save();

        if($set->status == 'lead') {
            return $response->withRedirect('/leads-auto/show/'.$set->id, 301);
        } else {
            return $response->withRedirect('/', 301);
        }
    }
}