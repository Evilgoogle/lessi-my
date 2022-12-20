<?php
namespace App\Support\LeadAuto\Sources;

use App\Models\Mail\Mail;
use PHPHtmlParser\Dom;
use App\Support\LeadAuto\Data\LeadCreator;
use App\Support\LeadAuto\Data\Activity\ActivityMail;

class CreateFromMail
{
    private $lead = null;
    private $mail;

    private $parse_email;
    private $parse_phone;

    public function run(Mail $mail)
    {
        $this->mail = $mail;

        $this->parse_email = $this->mobile_de_mail();
        $this->parse_phone = $this->mobile_de_phone();

        $this->setLead();
        $this->setActivity();
    }

    private function setLead() {
        if ($this->mail->direction == 0 && $this->mail->source == 1) {

            if (!empty($this->mail->from)) {

                $this->lead = new LeadCreator($this->mail->from, 'email');
                $this->lead->run();

                if (!empty($this->parse_phone)){
                    $this->lead->lead->contacts()->firstOrCreate(['type' => 'phone', 'value' => $this->parse_phone]);
                }
            } else {

                if (!empty($this->parse_phone)) {

                    $this->lead = new LeadCreator($phone, 'phone');
                    $this->lead->run();

                    if ($this->parse_email) {
                        $this->lead->lead->contacts()->firstOrCreate(['type' => 'email', 'value' => $this->parse_email]);
                    }
                } elseif ($this->parse_email) {

                    $this->lead = new LeadCreator($this->parse_email, 'email');
                    $this->lead->run();
                }
            }
        } else {
            $this->lead = new LeadCreator($this->mail->direction == 0 ? $this->mail->from : $this->mail->to, 'email');
            $this->lead->run();
        }

        if ($this->mail->source == 1) {
            $this->mobile_de_details();
        }
    }

    private function setActivity()
    {
        $get = new ActivityMail($this->lead->lead, $this->mail, 'mail', $this->mail->source);
        $get->run();
    }

    private function mobile_de_phone()
    {
        $parser	= new \PhpMimeMailParser\Parser();
        $parser->setText($this->mail->payload);
        $htmlEmbedded	 = $parser->getMessageBody('htmlEmbedded');

        $dom	 = new \DOMdocument();
        @$dom->loadHTML($htmlEmbedded);
        $xpath	 = new \DOMXPath($dom);

        $els = $xpath->query("//table[@bgcolor='#fee5b2']/tr/td/table/tr/td/table/tr/td/table");

        $re = '/Telefonnummer: (?\'phone\'[0-9]+)/m';

        $phone = null;
        foreach ($els as $el)
        {
            $matches = [];
            preg_match($re, $el->textContent, $matches);
            if (isset($matches['phone']))
                $phone	 = $matches['phone'];
        }


        return $phone;
    }

    private function mobile_de_mail()
    {
        $parser		 = new \PhpMimeMailParser\Parser();
        $parser->setText($this->mail->payload);
        $htmlEmbedded	 = $parser->getMessageBody('htmlEmbedded');

        $dom	 = new \DOMdocument();
        @$dom->loadHTML($htmlEmbedded);
        $xpath	 = new \DOMXPath($dom);

        $els = $xpath->query("//table[@bgcolor='#fee5b2']/tr/td/table/tr/td/table/tr/td/table");

        $re = '/Absender:(\s+)?(?\'email\'[0-9a-z-_.]+@[0-9a-z-_.]+)/m';

        $phone = null;
        foreach ($els as $el)
        {

            $matches = [];
            preg_match($re, $el->textContent, $matches);
            if (isset($matches['email']))
                $phone	 = $matches['email'];
        }


        return $phone;
    }

    private function mobile_de_details()
    {
        $parser		 = new \PhpMimeMailParser\Parser();
        $parser->setText($this->mail->payload);
        $htmlEmbedded	 = $parser->getMessageBody('htmlEmbedded');
        preg_match('/<a href="(?<link>[^"\']+)">Details aufrufen<\/a>/m', $htmlEmbedded, $res);

        if (isset($res['link']))
        {
            $page	 = [];
            exec('C:\node_script\phantomjs.exe C:\node_script\get_page.js ' . $res['link'], $page);
            $html	 = implode('', $page);

            $dom	 = new Dom;
            $dom->load($html);

            $rows	 = $dom->find('#contact .cBox-body .g-row');
            foreach ($rows as $row)
            {
                // get the class attr
                $class = $row->getAttribute('class');

                // do something with the html
                $html = $row->innerHtml;

                // or refine the find some more
                $child		 = $row->firstChild();
                $sibling	 = $child->nextSibling();
                $key		 = $child->firstChild()->text;
                $key		 = str_replace(':', '', mb_strtolower($key));
                $user_data[$key] = $sibling->text;
            }
            $car_data	 = $dom->find('#car-data');
            $note		 = $car_data->innerHTML;
            $lead_data	 = $dom->find('#lead-data');
            $note		 .= $lead_data->innerHTML;

            $this->mail->note	 = $note;
        }
    }
}