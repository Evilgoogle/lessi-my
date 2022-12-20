<?php
namespace App\Controllers\Filemanager;

class Paginator {

    public $paginate;
    public $start;

    public function go($model, $quantities, $conditions = null, $count = null) 
    {
        if($count === null) {
             $count_page = eval('return '.$model.'::paginator('.json_encode($conditions).');');
        } else {

            $count_page = $count;
        }

        $total = intval(($count_page - 1) / $quantities) + 1;
        if(!isset($_GET['page'])) {
            $_GET['page'] = 1;
        } elseif($_GET['page'] < 1) {
            $_GET['page'] = 1;
        }

        if($_GET['page'] >= $total) {
            $_GET['page'] = $total;
        }

        $this->paginate = [
            'page' => $_GET['page'],
            'total' => $total,
        ];

        $this->start = $_GET['page'] * $quantities - $quantities;
    }

    public function ajax($model, $quantities, $page, $conditions = null) {

        $count_page = eval('return '.$model.'::paginator('.json_encode($conditions).');');

        $total = intval(($count_page - 1) / $quantities) + 1;

        $this->paginate = [
            'page' => $page,
            'total' => $total,
        ];

        $this->start = $page * $quantities - $quantities;

    }
}