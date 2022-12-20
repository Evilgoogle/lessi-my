<?php
namespace App\Controllers\Checker\Libs;
use App\Models\Checker\AuftragKundenannahmeKundenhinweise;

class Helpers
{
    static function getKundenhinweise_auftrag($controller, $auftrag)
    {
        $strs = [];

        $start = false;
        foreach ($auftrag->positions as $pos) {
            $text = $controller->mb->convert(trim(strip_tags($pos->BEZEICHNUNG)));
            if (!$start) {
                if ($text == 'Kundenhinweise') {
                    $start = true;
                }
            } else {
                $re = '/(\d+\))(.+)/m';

                preg_match_all($re, $text, $matches, PREG_SET_ORDER, 0);
                if (empty($matches)) {
                    $start = false;
                } else {
                    $strs[] = $matches[0][0];
                }
            }
        }
        
        return [
            'auftrag_id' => $auftrag->id,
            'kundenhinweise' => $strs
        ];
    }
    
    static function tasks_complete($data) 
    {
        $isset = [];
        foreach($data as $t) {
            $child = $t->child();
            if(isset($child)) {
                $isset[] = $t->id;
            }
        }

        $result = false;
        if(count($isset) >= count($data)) {
            $result = true;
        }

        return $result;
    }
}