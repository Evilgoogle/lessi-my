<?php


namespace App\Controllers\Checker;


use App\Controllers\Controller;
use App\Core\Params;
use App\Models\Checker\Trip;
use Illuminate\Database\Capsule\Manager as DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\Paginator;
use Slim\Http\Request;
use Slim\Http\Response;
use Slim\Http\UploadedFile;

class CheckerApiController extends Controller
{
    public function index(Request $request, Response $response)
    {
        $page = (int)$request->getParam('page', 1);
        $limit = (int)$request->getParam('limit', 10);
        $search = $request->getParam('search');
        $additionalWhere = '';
        if ($search) {
            $search = strtolower($search);
            $search = '\'%'.$search.'%\'';
            $additionalWhere = 'AND (REPLACE(REPLACE(LOWER(FZH.POLKENNZEICHEN), \' \', \'\'), \'-\',\'\') LIKE '.$search;
            $additionalWhere .= ' OR LOWER(FZ.FAHRGESTELLNUMMER) LIKE '.$search;
            $additionalWhere .= ' OR LOWER(A.NAME1)  LIKE '.$search.')';
        }
        $skip = ($page - 1) * $limit;
        $mietwagens = DB::connection('macs')->select(
            DB::raw(
                'select
first '.$limit.' skip '.$skip.'
    MV.MIETDAUERVON,
    MV.MIETDAUERBIS,
    FZ.FAHRGESTELLNUMMER,
    FZH.POLKENNZEICHEN,
    KD.KUNDENNR,
    A.NAME1 KUNDENNAME,
    MA.NAME1 MITARBEITER,
    FZHST.HERSTELLERNAME,
    FZ.MODELLTEXT,
    FARBE.FARBEAUSSEN,
    KF.KRAFTSTOFFTEXT,
    KF.QUALITAET,
    KF.NORM
from MIETVERTRAG MV
join MIETSTATUS MS on MS.MIETSTATUSID = MV.MIETSTATUSID
join MIETWAGEN MW on MW.MIETWAGENID = MV.MIETWAGENID
join FAHRZEUG FZ on FZ.FAHRZEUGID = MW.FAHRZEUGID
join HERSTELLER FZHST on FZHST.HERSTELLERID = FZ.HERSTELLERID
join MODELL FZM on FZ.MODELLID = FZM.MODELLID
join FAHRZEUGHISTORIE FZH on FZH.FAHRZEUGHISTORIEID = FZ.FAHRZEUGHISTORIEIDAKTUELL
join KUNDE KD on KD.KUNDEID = MV.KUNDEID
join ADRESSE A on A.ADRESSEID = KD.ADRESSEID
join MITARBEITER MB on MB.MITARBEITERID = MV.MITARBEITERID
join ADRESSE MA on MA.ADRESSEID = MB.ADRESSEID
join FARBE on FZ.FARBEID = FARBE.FARBEID
left join KRAFTSTOFF KF on KF.KRAFTSTOFFID = FZ.KRAFTSTOFFID
where MV.MIETDAUERVON > dateadd(day, -7, current_timestamp)
'.$additionalWhere . ' ORDER BY MV.MIETDAUERVON ASC'
            )
        );

        $countMietwagens = DB::connection('macs')->selectOne(
            Db::raw(
                'SELECT COUNT(KD.KUNDENNR) 
from MIETVERTRAG MV
join MIETSTATUS MS on MS.MIETSTATUSID = MV.MIETSTATUSID
join MIETWAGEN MW on MW.MIETWAGENID = MV.MIETWAGENID
join FAHRZEUG FZ on FZ.FAHRZEUGID = MW.FAHRZEUGID
join HERSTELLER FZHST on FZHST.HERSTELLERID = FZ.HERSTELLERID
join MODELL FZM on FZ.MODELLID = FZM.MODELLID
join FAHRZEUGHISTORIE FZH on FZH.FAHRZEUGHISTORIEID = FZ.FAHRZEUGHISTORIEIDAKTUELL
join KUNDE KD on KD.KUNDEID = MV.KUNDEID
join ADRESSE A on A.ADRESSEID = KD.ADRESSEID
join MITARBEITER MB on MB.MITARBEITERID = MV.MITARBEITERID
join ADRESSE MA on MA.ADRESSEID = MB.ADRESSEID
join FARBE on FZ.FARBEID = FARBE.FARBEID
left join KRAFTSTOFF KF on KF.KRAFTSTOFFID = FZ.KRAFTSTOFFID
    where MV.MIETDAUERVON > dateadd(day, -7, current_timestamp) 
    '.$additionalWhere
            )
        );
        foreach ($mietwagens as $key => $mietwagen) {
            $mietwagen->POLKENNZEICHEN = trim(utf8_encode($mietwagen->POLKENNZEICHEN));

            $m = Trip::query()
                ->where('polkennzeichen', $mietwagen->POLKENNZEICHEN)
                ->where('fin', $mietwagen->FAHRGESTELLNUMMER)
                ->where('kundenr', $mietwagen->KUNDENNR)
                ->first();
            if ($m) {
                unset($mietwagens[$key]);
                continue;
            }
            $mietwagen->KUNDENNAME = trim(utf8_encode($mietwagen->KUNDENNAME));
            $mietwagen->MITARBEITER = trim(utf8_encode($mietwagen->MITARBEITER));
            $mietwagen->HERSTELLERNAME = trim(utf8_encode($mietwagen->HERSTELLERNAME));
            $mietwagen->MODELLTEXT = trim(utf8_encode($mietwagen->MODELLTEXT));
        }
        $mietwagens = array_values($mietwagens);

        return $response->withJson(
            [
                'items' => $mietwagens,
                'count' => $countMietwagens->COUNT ?? 0,
            ],
            null,
            JSON_INVALID_UTF8_IGNORE
        );
    }


    public function processed(Request $request, Response $response)
    {
        $page = $request->getParam('page') ?: 1;
        Paginator::currentPageResolver(
            function () use ($page) {
                return $page;
            }
        );
        $search = $request->getParam('search');

        $query = Trip::query()->where('is_finished', 0);
        if ($search) {
            $query->where(
                function (Builder $query) use ($search) {
                    $searchParam = '%'.strtolower($search).'%';
                    $query
                        ->orWhereRaw(
                            'REPLACE(REPLACE(LOWER(polkennzeichen), \' \', \'\'), \'-\',\'\') LIKE (?)',
                            [$searchParam]
                        )
                        ->orWhereRaw('lower(fin) LIKE (?)', [$searchParam])
                        ->orWhereRaw('lower(kundenr) LIKE (?)', [$searchParam]);
                }
            );
        }
        $trips = $query->paginate();

        $conditions = array_map(
            function ($trip) {
                $condition = '(';
                $condition .= 'FZH.POLKENNZEICHEN = \''.$trip->polkennzeichen.'\' ';
                $condition .= 'AND FZ.FAHRGESTELLNUMMER = \''.$trip->fin.'\' ';
                $condition .= 'AND KD.KUNDENNR = '.$trip->kundenr;
                $condition .= ' )';

                return $condition;
            },
            $trips->items()
        );
        $conditions = implode('OR ', $conditions);
        if (strlen($conditions) > 0) {
            $conditions = 'WHERE (' . $conditions . ')';
        }
        $mietwagens = DB::connection('macs')->select(
            DB::raw(
                'select
   MV.MIETDAUERVON,
    MV.MIETDAUERBIS,
    FZ.FAHRGESTELLNUMMER,
    FZH.POLKENNZEICHEN,
    KD.KUNDENNR,
    A.NAME1 KUNDENNAME,
    MA.NAME1 MITARBEITER,
    FZHST.HERSTELLERNAME,
    FZ.MODELLTEXT,
    FARBE.FARBEAUSSEN,
    KF.KRAFTSTOFFTEXT,
    KF.QUALITAET,
    KF.NORM
from MIETVERTRAG MV
join MIETSTATUS MS on MS.MIETSTATUSID = MV.MIETSTATUSID
join MIETWAGEN MW on MW.MIETWAGENID = MV.MIETWAGENID
join FAHRZEUG FZ on FZ.FAHRZEUGID = MW.FAHRZEUGID
join HERSTELLER FZHST on FZHST.HERSTELLERID = FZ.HERSTELLERID
join MODELL FZM on FZ.MODELLID = FZM.MODELLID
join FAHRZEUGHISTORIE FZH on FZH.FAHRZEUGHISTORIEID = FZ.FAHRZEUGHISTORIEIDAKTUELL
join KUNDE KD on KD.KUNDEID = MV.KUNDEID
join ADRESSE A on A.ADRESSEID = KD.ADRESSEID
join MITARBEITER MB on MB.MITARBEITERID = MV.MITARBEITERID
join ADRESSE MA on MA.ADRESSEID = MB.ADRESSEID
join FARBE on FZ.FARBEID = FARBE.FARBEID
left join KRAFTSTOFF KF on KF.KRAFTSTOFFID = FZ.KRAFTSTOFFID
     '.$conditions.'
ORDER BY MV.MIETDAUERBIS'
            )
        );


        foreach ($mietwagens as $mietwagen) {
            $mietwagen->POLKENNZEICHEN = trim(utf8_encode($mietwagen->POLKENNZEICHEN));
            $mietwagen->KUNDENNAME = trim(utf8_encode($mietwagen->KUNDENNAME));
            $mietwagen->MITARBEITER = trim(utf8_encode($mietwagen->MITARBEITER));
            $mietwagen->HERSTELLERNAME = trim(utf8_encode($mietwagen->HERSTELLERNAME));
            $mietwagen->MODELLTEXT = trim(utf8_encode($mietwagen->MODELLTEXT));

            foreach ($trips as $trip) {
                if ($mietwagen->POLKENNZEICHEN == $trip->polkennzeichen
                    && $mietwagen->FAHRGESTELLNUMMER == $trip->fin
                    && $mietwagen->KUNDENNR == $trip->kundenr
                ) {
                    $mietwagen->crosses = $trip->crosses;
                    $mietwagen->data = $trip->data;
                    $mietwagen->km_stand = $trip->km_stand;
                    break;
                }
            }
        }

        return $response->withJson(
            [
                'items' => $mietwagens,
                'count' => $trips->total() ?? 0,
            ],
            null,
            JSON_INVALID_UTF8_IGNORE
        );
    }

    public function postUploadImage(Request $request, Response $response)
    {
        $dir = Params::getParam('public_dir').'/upload/checker';

        if (!is_dir($dir)) {
            mkdir($dir);
        }
        $files = $request->getUploadedFiles();
        if (!isset($files['file'])) {
            return $response->withJson(null);
        }
        /** @var UploadedFile $file */
        $file = $files['file'];
        $dividedFileName = explode('.', $file->getClientFilename());
        $extension = strtolower(array_pop($dividedFileName));
        if (!in_array($extension, ['png', 'jpg', 'jpeg'])) {
            return $response->withJson(null);
        }

        $fileName = md5(uniqid('random_file', true)).'.'.$extension;
        $file->moveTo($dir.'/'.$fileName);

        return $response->withJson($fileName);
    }

    public function saveData(Request $request, Response $response)
    {
        $polkennzeichen = $request->getParam('polkennzeichen');
        $fin = $request->getParam('fin');
        $kundenr = $request->getParam('kundenr');
        $trip = Trip::query()
            ->where('polkennzeichen', $polkennzeichen)
            ->where('fin', $fin)
            ->where('kundenr', $kundenr)
            ->first() ?: new Trip();
        $trip->fill(
            [
                'polkennzeichen' => $polkennzeichen,
                'fin' => $fin,
                'kundenr' => $kundenr,
                'crosses' => $request->getParam('crosses'),
                'data' => $request->getParam('data'),
                'km_stand' => $request->getParam('km_stand'),
            ]
        );
        $trip->save();

        return $response->withJson(true);
    }

    public function saveProcessedData(Request $request, Response $response)
    {
        $polkennzeichen = $request->getParam('polkennzeichen');
        $fin = $request->getParam('fin');
        $kundenr = $request->getParam('kundenr');
        $trip = Trip::query()
            ->where('polkennzeichen', $polkennzeichen)
            ->where('fin', $fin)
            ->where('kundenr', $kundenr)
            ->first();

        if (!$trip) {
            return $response->withJson(false);
        }

        $trip->fill(
            [
                'return_crosses' => $request->getParam('crosses'),
                'return_data' => $request->getParam('data'),
                'is_finished' => true,
                'return_km_stand' => $request->getParam('km_stand'),
            ]
        );
        $trip->save();

        return $response->withJson(true);
    }

}