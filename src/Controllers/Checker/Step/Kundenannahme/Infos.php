<?php
namespace App\Controllers\Checker\Step\Kundenannahme;

use App\Controllers\Controller;
use Illuminate\Pagination\Paginator;
use App\Models\MACS;
use App\Models\Checker\AuftragKundenannahmeInfoTag;

class Infos extends Controller
{
    private function checkPermission($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('checker.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
    }

    public function set($request, $response)
    {
        if($request->getParam('sonstige') == 0) {
            $query = AuftragKundenannahmeInfoTag::find($request->getParam('id'));

            if($query->set == 1) {
                $query->set = 0;
            } elseif($query->set == 0) {
                $query->set = 1;
            }

            $query->desc = $request->getParam('desc');
            $query->save();
        } else if($request->getParam('sonstige') == 1) {

            $query = new AuftragKundenannahmeInfoTag();
            $query->auftragnr = $request->getParam('id');
            $query->desc = $request->getParam('desc');
            $query->sonstige = 1;
            $query->save();
        }

        return $response->withJson([
            'query' => $query,
            'templete' => $this->view->fetch('checker/part/kundenannahme/infos/tag.twig', ['item' => $query, 'sonstige' => ($request->getParam('sonstige') == 1) ? true : false])
        ]);
    }

    public function remove($request, $response)
    {
        AuftragKundenannahmeInfoTag::where('id', $request->getParam('id'))->delete();

        return $response->withJson('ok');
    }

    public function init($auftrag)
    {
        $arr = ['Wartekunde','Dialogannahme gewünscht',/*'Problemkunde','Verkaufsberatung erwünscht'*/];
        $get = AuftragKundenannahmeInfoTag::where('auftragnr', $auftrag->auftragnr)->first();
        if(!isset($get)) {
            foreach($arr as $a) {
                $set = new AuftragKundenannahmeInfoTag();
                $set->auftragnr = $auftrag->AUFTRAGSNR;
                $set->tag = $a;
                $set->save();
            }
        }
    }
}
