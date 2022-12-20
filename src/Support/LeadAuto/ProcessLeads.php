<?php
namespace App\Support\LeadAuto;

use App\Models\LeadAuto\EastwoodLead;
use App\Models\Ats\Call;
use App\Models\Mail\Mail;
use App\Models\MACS\Kontakt;

use App\Support\LeadAuto\Sources\CreateFromCall;
use App\Support\LeadAuto\Sources\CreateFromHWS;
use App\Support\LeadAuto\Sources\CreateFromMacs;
use App\Support\LeadAuto\Sources\CreateFromMail;

/**
 * Description of ProcessLeads
 *
 * @author Evilgoogle
 */
class ProcessLeads
{
    public function run(\DateTime $date)
    {
        $date2	 = clone $date;

        $begin	 = /*'2022-02-10 12:00:00';*/ $date->format('Y-m-d H:i:s');
        $end	 = /*'2022-02-10 18:00:00';*/ $date->modify('+1 day')->format('Y-m-d H:i:s');

        print $begin . PHP_EOL;

        $this->createCall($begin, $end);
        $this->createMail($begin, $end);
        $this->createMacs($date2);
        $this->createHWS($begin, $end);
    }

    private function createCall($begin, $end)
    {
        $aCreator = new CreateFromCall();
        $calls_cursor = Call::whereBetween('date_time', [$begin, $end])->orderBy('date_time')->cursor();

        foreach ($calls_cursor as $row) {
            $aCreator->run($row);
        }
    }

    private function createMail($begin, $end)
    {
        $aCreator = new CreateFromMail();
        $calls_cursor = Mail::whereBetween('date_time', [$begin, $end])->orderBy('date_time')->cursor();

        foreach ($calls_cursor as $row) {
            $aCreator->run($row);
        }
    }

    private function createMacs($date)
    {
        $aCreator = new CreateFromMacs();
        $begin = /* '10-02-2022 12:00:00'; */ $date->format('d.m.Y');
        $end = /* '10-02-2022 18:00:00'; */   $date->modify('+1 day')->format('d.m.Y');

        $query = Kontakt::
        select(
            'el.DEALERSTATUS',
            'el.LEADTYPE',
            'el.STATUS',
            'el.EDEALERLEADID as id',
            'el.STARTDATE',
            'el.MITARBEITERID',
            'el.HOMEPHONE',
            'el.CELLULARPHONE',
            'el.PERSONALEMAIL',
            'el.WORKEMAIL',
            'el.OPPORTUNITYNOTES'
        )
            ->leftjoin('EDEALERLEAD as el', 'KONTAKT.EDEALERLEADID', '=', 'el.EDEALERLEADID')
            ->whereBetween('KONTAKT.ANLAGEDAT', [$begin, $end])
            ->where('KONTAKT.MANDANTID', 1)
            ->whereNull('KONTAKT.ZUVERTEILEN')
            ->whereIn('KONTAKT.KONTAKTTYPID', [3])
            ->get();

        foreach ($query as $row) {
            $aCreator->run($row);
        }
    }

    private function createHWS($begin, $end)
    {
        $aCreator = new CreateFromHWS();

        $eLeads	= EastwoodLead::whereBetween('date', [$begin, $end])
            ->whereNotIn('form', ['Werkstatttermin', 'fzid'])
            ->get();

        foreach ($eLeads as $item) {
            $aCreator->run($item);
        }
    }
}
