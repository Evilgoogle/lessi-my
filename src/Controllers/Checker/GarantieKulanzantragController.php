<?php
namespace App\Controllers\Checker;

use App\Models\Checker\GarantieKulanzantrag;
use App\Models\Checker\GarantieKulanzantragFilemanager;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;
/**
 * Description of GarantieKulanzantragController
 *
 * @author joker
 */
class GarantieKulanzantragController extends Controller
{
    private function dateConvert($date)
    {
        if(!empty($date)) {
            preg_match('#([0-9]*)\.([0-9]*)\.([0-9]*)#ui', $date, $match);

            return $match[3].'-'.$match[2].'-'.$match[1].' 00:00:00';
        } else {
            return null;
        }
    }

    public function insert($request, $response)
    {
        $array = [
            'desc' => v::notEmpty(),
            'file' => v::notEmpty(),
            'garantietrager' => v::notEmpty()
        ];
        if($request->getParam('garantietrager') == 'Garantieversicherung') {
            $array['trager_name'] = v::notEmpty();
            $array['trager_nr'] = v::notEmpty();
        }
        $validation = $this->validator->validate($request, $array);

        if (!$validation->failed()) {
            $set = new GarantieKulanzantrag();
            $set->desc = $request->getParam('desc');
            $set->file = $request->getParam('file');
            $set->garantietrager = $request->getParam('garantietrager');
            $set->trager_name = $request->getParam('trager_name') !== null ? $request->getParam('trager_name') : null;
            $set->trager_nr = $request->getParam('trager_nr') !== null ? $request->getParam('trager_nr') : null;
            $set->auftragsnummer = $request->getParam('auftragsnummer');
            $set->anfrage_create = $this->dateConvert($request->getParam('anfrage_create'));
            $set->anfrage_type = $request->getParam('anfrage_type');
            $set->genehmigt_am = $this->dateConvert($request->getParam('genehmigt_am'));
            $set->genehmigt_in = !empty($request->getParam('genehmigt_in')) ? $request->getParam('genehmigt_in') : null;
            $set->abgelehnt = $request->getParam('abgelehnt') !== null ? $this->dateConvert($request->getParam('abgelehnt')) : null;
            $set->save();

            foreach(explode(',', $request->getParam('file')) as $id) {
                $item = GarantieKulanzantragFilemanager::find($id);
                $item->external_id = $set->id;
                $item->save();
            }

            $del = GarantieKulanzantragFilemanager::where('insert_page', 'id')->whereNull('external_id')->get();
            foreach($del as $d) {
                @unlink('./static/files/checker/garantie_kulanzantrag'.$d->file);
                $d->delete();
            }

            return $response->withJson('ok');
        }

        return $response->withJson($validation->getErrors(), 422);
    }

    public function edit($request, $response)
    {
        $items = GarantieKulanzantrag::find($request->getParam('id'));

        return $response->withJson($items);
    }

    public function update($request, $response)
    {
        $set = GarantieKulanzantrag::find($request->getParam('id'));
        $set->desc = $request->getParam('desc');
        $set->file = !empty($request->getParam('file')) ? $request->getParam('file') : null;
        $set->garantietrager = $request->getParam('garantietrager');
        $set->trager_name = $request->getParam('trager_name') !== null ? $request->getParam('trager_name') : null;
        $set->trager_nr = $request->getParam('trager_nr') !== null ? $request->getParam('trager_nr') : null;
        $set->auftragsnummer = $request->getParam('auftragsnummer');
        $set->anfrage_create = $this->dateConvert($request->getParam('anfrage_create'));
        $set->anfrage_type = $request->getParam('anfrage_type');
        $set->genehmigt_am = $request->getParam('genehmigt_am') !== null ? $this->dateConvert($request->getParam('genehmigt_am')) : null;
        $set->genehmigt_in = $request->getParam('genehmigt_in') !== null ? $request->getParam('genehmigt_in') : null;
        $set->abgelehnt = $request->getParam('abgelehnt') !== null ? $this->dateConvert($request->getParam('abgelehnt')) : null;
        $set->save();

        if(!empty($request->getParam('file'))) {
            foreach(explode(',', $request->getParam('file')) as $id) {
                $item = GarantieKulanzantragFilemanager::find($id);
                $item->external_id = $set->id;
                $item->save();
            }
        }

        $del = GarantieKulanzantragFilemanager::where('insert_page', 'id')->whereNull('external_id')->get();
        foreach($del as $d) {
            @unlink('./static/files/checker/garantie_kulanzantrag'.$d->file);
            $d->delete();
        }

        return $response->withJson('ok');
    }
}
