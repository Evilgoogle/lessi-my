<?php
namespace App\Support\LeadAuto\Libs;

use App\Models\LeadAuto\Lead;
use App\Models\MACS\Kommunication;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * Description of LeadDataSeeker
 *
 * @author joker
 */
class LeadDataSeeker
{
    static function escape_string_clear($term)
    {
        $reservedSymbols = ['<', '>', '(', ')', '~', '\'', '\"', '/*', '*/'];
        return str_replace($reservedSymbols, '', $term);
    }

    static function findLeadByContact($contact)
    {
        if (strlen($contact) < 5) {
            return null;
        }

        // Cырой запрос написан по причине бага в ORM (идут дубликаты)
        $lead = DB::select(DB::raw('select '
            . '`lead_autos`.* from `lead_autos` '
            . 'inner join `lead_auto_contacts` as `c` on `c`.`lead_id` = `lead_autos`.`id` '
            . 'where `c`.`value` = "'.self::escape_string_clear($contact).'" limit 1'
        ));

        if(count($lead) == 0) {
            return null;
        } else {
            return Lead::find($lead[0]->id);
        }
    }

    static function findLeadInMACS($contact, $contact_type)
    {
        if (strlen($contact) < 5) {
            return null;
        }

        $query = Kommunication::
        select('k.KUNDEID', 'el.EDEALERLEADID', 'i.INTERESSENTID', 'a.ADRESSEID', 'a.NAME1', 'KOMMUNIKATION.KOMM_TEXT')
            ->join('ADRESSE as a', 'a.ADRESSEID', '=', 'KOMMUNIKATION.ADRESSEID')
            ->join('KUNDE as k', 'k.ADRESSEID', '=', 'a.ADRESSEID')
            ->join('EDEALERLEAD as el', 'el.ADRESSEID', '=', 'a.ADRESSEID')
            ->join('INTERESSENT as i', 'i.ADRESSEID', '=', 'a.ADRESSEID')
            ->orderBy('k.KUNDEID', 'DESC');

        if ($contact_type == 'email') {

            $query->whereRaw('trim(KOMMUNIKATION.KOMM_TEXT) = \'' . $contact . '\'');
        } else {

            $query->whereRaw('trim(KOMMUNIKATION.SUCHNR) LIKE \'%' . $contact . '\'');
        }

        return $query->first();
    }

    static function getContactsInMacs($adresse_id)
    {
        $contacts_from_adresse = Kommunication::where('ADRESSEID', $adresse_id)->get();

        $data = [];
        foreach ($contacts_from_adresse as $komm) {
            if (strlen($komm['KOMM_TEXT']) < 5) {
                continue;
            }
            if (in_array($komm['KOMM_ARTID'], range(1, 6))) {
                $data[] = [
                    'type' => 'phone',
                    'value' => preg_replace('/^(0049|[0]{1}|\+49)/', '', trim($komm['SUCHNR']))
                ];
            } else {
                $komm['KOMM_TEXT'] = mb_convert_encoding(trim($komm['KOMM_TEXT']), 'utf8', 'iso-8859-1');
                $data[] = [
                    'type' => 'email',
                    'value' => $komm['KOMM_TEXT'],
                ];
            }
        }
        return $data;
    }
}
