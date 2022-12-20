<?php
namespace App\Controllers\Leasback\Steps\Einladung;

use App\Controllers\Controller;
use PHPMailer\PHPMailer\PHPMailer;
use App\Models\LeasBack\StepStatus;
use App\Models\LeasBack\StepStatusChild;
use App\Models\LeasBack\LeasBack;

/**
 * Description of Einladung
 *
 * @author EvilGoogle
 */
class Mail extends Controller
{
    public function send($request, $response)
    {
        $title = 'Termin';
        $message = 'Confirmation letter';
        $to = LeasBack::find($request->getParam('leasback_id'))->email;
       
        $mail = new PHPMailer(true);
        $mail->CharSet		 = "UTF-8";
        $mail->SMTPDebug	 = 0;   // Enable verbose debug output
        $mail->isSMTP();  // Set mailer to use SMTP
        $mail->Host		 = 'kc.lessing.gmbh';  // Specify main and backup SMTP servers
        $mail->SMTPAuth		 = false; // Enable SMTP authentication
        $mail->Username		 = 'termin@al-auto.de';  // SMTP username
        $mail->Password		 = 'Mazda777!!!';
        $mail->SMTPSecure	 = false;
        $mail->SMTPAutoTLS	 = false;
        $mail->Port		 = 25;   // TCP port to connect to
        //Recipients
        $mail->setFrom('termin@al-auto.de');
        $mail->addAddress($to);  // Add a recipient
        
        $attach = $this->renderIcs($to);//dd($attach);
        !$attach ?: $mail->addAttachment($attach);

        $mail->isHTML(true); // Set email format to HTML
        $mail->Subject	 = $title;
        $mail->Body	 = $message;

        $mail->send();
        
        $result = $this->child($request->getParam('leasback_id'));
        
        return $response->withJson($result->status);
    }
    
    private function child($leasback_id)
    {
        $step = StepStatus::where('leasback_id', $leasback_id)->where('step', 'einladung')->first();
        
        $set = StepStatusChild::where('step_id', $step->id)->where('step', 'mail')->first();
        $set->status = 'success';
        $set->save();
        
        return $set;
    }
    
    private function renderIcs($email)
    {
        $dateBase = new \DateTime();
        
        $period = 30;

        $date		 = new \DateTime();
        $uid		 = md5(uniqid('ics_file', true));
        $fileName	 = $uid . '.ics';
        $startDate	 = $dateBase->format('Ymd') . 'T' . $dateBase->format('His');
        $endDate	 = $dateBase;
        $endDate->modify('+' . $period . ' minutes');
        $endDate	 = $dateBase->format('Ymd') . 'T' . $endDate->format('His');
        $icsContent	 = "BEGIN:VCALENDAR
PRODID:-//Kerio Technologies//Outlook Connector//EN
METHOD:REQUEST
VERSION:2.0
X-VERSION-KMS:6.2.0
BEGIN:VTIMEZONE
TZID:Amsterdam, Belgrade, Berlin, Brussels, Budapest, Madrid,
  Paris, Prague, Stockholm
BEGIN:STANDARD
DTSTART:19961027T030000
TZOFFSETTO:+0100
TZOFFSETFROM:+0200
RRULE:FREQ=YEARLY;BYMONTH=10;BYDAY=-1SU
END:STANDARD
BEGIN:DAYLIGHT
DTSTART:19810329T020000
TZOFFSETTO:+0200
TZOFFSETFROM:+0100
RRULE:FREQ=YEARLY;BYMONTH=3;BYDAY=-1SU
END:DAYLIGHT
END:VTIMEZONE
BEGIN:VEVENT
DTSTAMP:" . $date->format('YmdTHis') . "
UID:$uid
DESCRIPTION: \n
PRIORITY:5
SUMMARY:Räderwechseltermin
LOCATION:Autohaus Lessingstraße GmbH Lessingstr. 12 Oberhausen
TRANSP:OPAQUE
X-MICROSOFT-CDO-BUSYSTATUS:TENTATIVE
X-LABEL:0
CLASS:PUBLIC
X-MICROSOFT-CDO-INTENDEDSTATUS:BUSY
SEQUENCE:0
ORGANIZER;CN=\"Autohaus Lessingstraße GmbH\":mailto:service@al-auto.de
ATTENDEE;RSVP=TRUE;X-SENT=TRUE;CN=$email;CUTYPE=INDIVIDUAL:mailto:$email
X-ALARM-TRIGGER:-PT15M
DTSTART;TZID=\"Amsterdam, Belgrade, Berlin, Brussels, Budapest, Madrid, Paris, Prague, Stockholm\":$startDate
DTEND;TZID=\"Amsterdam, Belgrade, Berlin, Brussels, Budapest, Madrid, Paris, Prague, Stockholm\":$endDate
END:VEVENT
END:VCALENDAR";

        $directory = $_SERVER['DOCUMENT_ROOT'].'/static/files/leasback/';//=  C:\xampp\htdocs\dev\joker\src/../public/temp/d0cfca75b5d444ce93347d783c3a4f50.ics
        $filePath = $directory . $fileName;
        file_put_contents($filePath, $icsContent);
        
        return $filePath;
    }
}
