<?php
namespace App\Support\LeadAuto\Libs;

use PHPMailer\PHPMailer\PHPMailer;

/**
 * Description of MailSender
 *
 * @author joker
 */
class Timer
{
    public $time;

    public $hash = null;
    public $lead_id = null;
    public $user_id = null;

    public function __construct($timer = '+2 minute')
    {
        $this->time = new \DateTime();
        $this->time->add(\DateInterval::createFromDateString($timer));
    }

    public function create()
    {
        if(isset($this->hash) && isset($this->lead_id) && isset($this->user_id)) {
            $cmd = 'SCHTASKS /Create /RU "FALTYN\DS" /RP "Knupi777" /RL "HIGHEST" /ST "'.$this->time->format('H:i').'" /tn "Lessi LeadsAuto\timer_'.$this->lead_id.'_'.$this->user_id.'_'.$this->id.'" /sc "ONCE" /tr "C:\xampp\php\php.exe -r file_get_contents(\\\""http://les.faltyn.local/cron/leads-auto/re-distribute/'.$this->hash.'/'.$this->lead_id.'/'.$this->user_id.'/'.$this->id.'\\\""");"';

            exec($cmd);
        }
    }

    static function destroy($lead_id, $user_id, $id)
    {
        $cmd = 'SCHTASKS /Delete /TN "Lessi LeadsAuto\timer_'.$lead_id.'_'.$user_id.'_'.$id.'" /F';

        exec($cmd);
    }
}
