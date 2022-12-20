<?php
namespace App\Controllers\Checker;

use App\Controllers\Controller;
use App\Models\MACS\Auftrag;
use App\Models\Checker\Termin;

/**
 * Description of TerminController
 *
 * @author EvilGoogle
 */
class TerminController extends Controller
{
    public function list($request, $response)
    {
        $this->checkPermission($request, $response);

        $begin = (new \DateTime())->modify('+1 day')->format('d.m.Y').' 00:00:00';
        $end = (new \DateTime())->modify('+2 day')->format('d.m.Y').' 00:00:00';

        $auftrags = Auftrag::whereBetween('ANLIEFERUNG', [$begin, $end])
            ->whereIN('TYP', [0, 2])
            ->where('AUFTRAGSNR', '!=', 10018)
            ->get();

        $wspakets = [];
        foreach($auftrags as $a) {
            $arr = collect();
            foreach($a->positions as $p) {
                if($p->paketPosition != null) {
                    $arr->push($this->mb->convert($p->paketPosition->paket->PAKETBEZ));
                }
            }
            $arr = $arr->unique();

            $wspakets[] = [
                'AUFTRAGID' => $a->AUFTRAGID,
                'pakets' => $arr
            ];
        }

        $this->view->render($response, 'checker/termin.twig', ['auftrags' => $auftrags, 'wspakets' => $wspakets, 'begin' => $begin]);
    }

    public function datas($request, $response)
    {
        $this->checkPermission($request, $response);

        $begin = $request->getParam('begin').' 00:00:00';
        $end = (new \DateTime($request->getParam('begin').' 00:00:00'))->modify('+1 day')->format('d.m.Y').' 00:00:00';

        $auftrags = Auftrag::whereBetween('ANLIEFERUNG', [$begin, $end])
            ->whereIN('TYP', [0, 2])
            ->where('AUFTRAGSNR', '!=', 10018)
            ->get();

        $wspakets = [];
        foreach($auftrags as $a) {
            $arr = collect();
            foreach($a->positions as $p) {
                if($p->paketPosition != null) {
                    $arr->push($this->mb->convert($p->paketPosition->paket->PAKETBEZ));
                }
            }
            $arr = $arr->unique();

            $wspakets[] = [
                'AUFTRAGID' => $a->AUFTRAGID,
                'pakets' => $arr
            ];
        }

        $this->view->render($response, 'checker/termin.twig', ['auftrags' => $auftrags, 'wspakets' => $wspakets, 'begin' => $begin]);
    }

    public function type($request, $response)
    {
        if($request->getParam('active') == 1) {

            $set = Termin::where('auftragnr', $request->getParam('auftragnr'))->where('type', $request->getParam('type'))->first();
            if(!$set) {
                $set = new Termin();
            }
            $set->auftragnr = $request->getParam('auftragnr');
            $set->type = $request->getParam('type');
            $set->save();

            if($set->type == 'app') {
                (new IndexController($this->container))->add($request, $response);
            }

            if($request->getParam('type') == 'vip') {
                $set = Termin::where('auftragnr', $request->getParam('auftragnr'))->where('type', 'app')->first();
                if(!$set) {
                    $set = new Termin();
                }
                $set->auftragnr = $request->getParam('auftragnr');
                $set->type = 'app';
                $set->save();

                (new IndexController($this->container))->add($request, $response);
            }
        } else {

            $del = Termin::where('auftragnr', $request->getParam('auftragnr'))->where('type', $request->getParam('type'))->first();
            if($del) {
                $del->delete();
            }

            if($request->getParam('type') == 'vip') {
                $del = Termin::where('auftragnr', $request->getParam('auftragnr'))->where('type', 'app')->first();
                if($del) {
                    $del->delete();
                }
            }
        }
    }

    public function type_contact($request, $response)
    {
        $set = Termin::where('auftragnr', $request->getParam('auftragnr'))->first();
        if(!$set) {
            $set = new Termin();
        }
        $set->auftragnr = $request->getParam('auftragnr');
        $set->type_contact = $request->getParam('type');
        $set->save();
    }

    private function checkPermission($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('checker.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
    }
}
