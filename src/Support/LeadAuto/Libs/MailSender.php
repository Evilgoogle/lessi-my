<?php
namespace App\Support\LeadAuto\Libs;

use App\Support\Mails;
use PHPMailer\PHPMailer\PHPMailer;

/**
 * Description of MailSender
 *
 * @author joker
 */
class MailSender
{
    private $type;
    private $from;
    private $to;

    private $title;
    private $message;
    private $host = 'http://lessing.p7.de:82'; //$_SERVER['HTTP_HOST'];

    public function __construct($type, array $to, $from = null, $title = null, $message = null)
    {
        $this->type = $type;
        $this->from = $from;
        $this->to = $to;

        $this->title = $title;
        $this->message = $message;
    }

    public function run()
    {
        if($this->type == 'new-lead') {

            $title = 'Hot Lead';
            $message = '<p>Yes, new hot lead!<br><a href="'.$this->host.'/leads-auto/hot">Open</a></p>';
            $this->sendmail($this->to[0], $title, $message);
        } elseif($this->type == 'transfer-lead') {

            $title = 'Lead has been taken';
            $message = '<p>Your lead has been taken!<br>';
            $this->sendmail($this->to[0], $title, $message);

            $title = 'Hot Lead';
            $message = '<p>Yes, new hot lead!<br><a href="'.$this->host.'/leads-auto/hot">Open</a></p>';
            $this->sendmail($this->to[1]);
        } elseif($this->type == 'mail') {

            $title = $this->title;
            $message = $this->message;
            $this->sendmail($this->to[0], $title, $message);
        }
    }

    private function sendmail($to, $title, $message)
    {
        // Test
        /*define("UsersFolder", "C:\\store\\mail\\al-auto.de");

        $dir = @scandir(UsersFolder);

        $sources = file(__DIR__ . '/sources.properties', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

        dd(UsersFolder, $dir, $sources);*/
        //

        try {
            /*$mail = new PHPMailer();

            $mail->CharSet       = "UTF-8";
            $mail->SMTPDebug     = 0;   // Enable verbose debug output
            $mail->isSMTP();  // Set mailer to use SMTP
            $mail->Host      = 'kc.lessing.gmbh';  // Specify main and backup SMTP servers
            $mail->SMTPAuth      = false; // Enable SMTP authentication
            $mail->Username      = 'termin@al-auto.de';  // SMTP username
            $mail->Password      = 'Mazda777!!!';
            $mail->SMTPSecure    = false;
            $mail->SMTPAutoTLS   = false;
            $mail->Port      = 25;   // TCP port to connect to
            $mail->setFrom('termin@al-auto.de');

            $mail->addAddress($to, 'New message');

            $mail->Subject = $title;
            $mail->isHTML(true);
            $mail->msgHTML($message);
            $mail->send();*/

            // Test 2
            /*$mail              = new PHPMailer(true);
            $mail->SMTPDebug   = false;
            $mail->isSMTP();
            $mail->Host        = 'kc.lessing.gmbh';
            $mail->SMTPAuth    = false;
            $mail->Username    = 'ds@al-auto.de';
            $mail->SMTPSecure  = false;
            $mail->SMTPAutoTLS = false;
            $mail->Port        = 25;
            $mail->setFrom($mail->Username, ('History Mail'));
            $mail->addAddress($to);
            $mail->isHTML(true);
            $mail->CharSet     = 'UTF-8';
            $mail->Subject     = $title;
            $mail->Body        = $message;
            $mail->send();*/

            Mails::send_mail($to, $message, $title, [], 'ds@al-auto.de', 'History Mail');

        } catch (\PHPMailer\PHPMailer\Exception $e) {
            dd($e);
        }
    }
}
