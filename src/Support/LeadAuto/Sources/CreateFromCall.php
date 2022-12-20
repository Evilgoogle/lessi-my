<?php
namespace App\Support\LeadAuto\Sources;

use App\Models\Ats\Call;
use App\Support\CallSupport;
use App\Support\LeadAuto\Data\LeadCreator;
use App\Support\LeadAuto\Data\LeadCreatorContact;
use App\Support\LeadAuto\Data\Activity\ActivityCall;

use App\Models\MACS\Auftrag;

class CreateFromCall
{
    public $methot_type;

    private $lead;
    private $call;

    /**
     *
     * @var target_numbers
     */
    private $sources = [
        [ 'num'=>'0208-309933-30', 'source'=>1, 'filial'=>'ob' ], // mobile.de
        [ 'num'=>'02541-92834-29', 'source'=>1, 'filial'=>'coe' ], // mobile.de
        [ 'num'=>'0208-309933-91', 'source'=>2, 'filial'=>'ob' ], // HWS
        [ 'num'=>'02541-92834-27', 'source'=>2, 'filial'=>'coe' ], // HWS
        [ 'num'=>'0208-309933-66', 'source'=>6, 'filial'=>'ob' ], // autoscout
        [ 'num'=>'02541-92834-28', 'source'=>6, 'filial'=>'coe' ], // autoscout
    ];

    /**
     *
     * @param Call $call
     * @return boolean
     */
    public function run (Call $call)
    {
        $this->call = $call;

        /*$caller_id  = CallSupport::extractCaller($call);
        if (!$caller_id)
            return false;
        if (CallSupport::ignoreList($call))
            return false;*/

        $this->setLead($this->call->caller);
        $this->setActivity();
    }

    private function setLead($caller_id)
    {
        $this->lead = new LeadCreator($caller_id, 'phone');
        $this->lead->run();
    }

    private function setActivity()
    {
        $source = $this->getSource();

        $get = new ActivityCall($this->lead->lead, $this->call, 'call', $source['source'], $source['filial']);
        $get->run();
    }

    private function getSource()
    {
        foreach ($this->sources as $tn) {
            $to = $this->call->direction == 'inbound' ? $this->call->service . '-' . $this->call->ddi : $this->call->caller;

            if ($to == $tn['num']) {
                return [
                    'source' => $tn['source'],
                    'filial' => $tn['filial'],
                ];
            } else {
                return [
                    'source' => null,
                    'filial' => null,
                ];
            }
        }
    }
}
