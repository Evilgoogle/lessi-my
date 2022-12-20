<?php
namespace App\Controllers\Filemanager;

use App\Controllers\Controller;
use App\Controllers\Filemanager\Paginator;
use Respect\Validation\Validator as v;
use Illuminate\Database\Capsule\Manager as DB;

class Index extends Controller
{

    public function upload($request, $response) 
    {
        try {
            $set = eval('return new '.$request->getParam('model').'();');
            $set->title = preg_replace('#\.[a-z]{3}#ui', '', $request->getParam('name'));

            $set->file = new Uploader('file');
            $set->file->rootUpload = $request->getParam('path');
            $set->file = $set->file->filemanagerUpload($request->getParam('file'));

            $set->type = $request->getParam('type');
            $set->size = $request->getParam('size');
            
            if($request->getParam('insert_page') == 'id') {
                $set->insert_page = $request->getParam('insert_page');
                $set->external_id = $request->getParam('external_id') != 'false' ? $request->getParam('external_id') : null;
            }

            $table = $set->getTable();
            foreach(DB::getSchemaBuilder()->getColumnListing($table) as $column) {
                foreach ($request->getParam('conds') as $cond) {
                    if($column == $cond[0]) {
                        $set->$column = $cond[2];
                    }
                }
            }
            $set->save();

            $this->view->render($response, 'filemanager/template/item.twig', [
                'set' => $set,
                'path' => $request->getParam('path')
            ]);

        } catch (\Exception $e) {
            return $response->withJson($e->getMessage(), 422);
        }
    }

    public function load($request, $response) 
    {
        $limit = 24;
        $paginate = new Paginator();
        $path = $request->getParam('path');

        if ($request->isXhr() && $request->getParam('page')) {
            $paginate->ajax($request->getParam('model'), $limit, $request->getParam('page'), $request->getParam('conds'));
            
            $query = $request->getParam('model')::where($request->getParam('conds'))
                ->orderBy('created_at', 'desc')
                ->offset($paginate->start)
                ->limit($limit);

            if($request->getParam('insert_page') == 'id') {
                if($request->getParam('external_id') != 'false') {
                    $query->where('external_id', $request->getParam('external_id'));
                } else {
                    $query->whereNull('external_id');
                }
            }

            $get = $query->get();

            return $response->withJson([
                'page' => $request->getParam('page'),
                'total'  => $paginate->paginate['total'],
                'view'  => $this->view->fetch('filemanager/template/ajax.twig', ['get' => $get, 'path' => $path])
            ]);
        } else {
            $paginate->go($request->getParam('model'), $limit, $request->getParam('conds'), null);
            $paginate_view = $paginate->paginate;

            $query = $request->getParam('model')::where($request->getParam('conds'))
                ->orderBy('created_at', 'desc')
                ->offset($paginate->start)
                ->limit($limit);

            if($request->getParam('insert_page') == 'id') {
                if($request->getParam('external_id') != 'false') {
                    $query->where('external_id', $request->getParam('external_id'));
                } else {
                    $query->whereNull('external_id');
                }
            }

            $get = $query->get();
        }

        return $response->withJson([
            'page' => $paginate_view['page'],
            'total'  => $paginate_view['total'],
            'view' => $this->view->fetch('filemanager/template/items.twig', ['get' => $get, 'paginate_view' => $paginate_view, 'path' => $path])
        ]);
    }

    public function crop($request, $response) 
    {
        try {
            $set = $request->getParam('model')::where('id', $request->getParam('id'))->first();
            $image = $set->file;

            $set->file = new Uploader('file');
            $set->file->rootUpload = $request->getParam('path');
            $get_file = $set->file->filemanagerUpload($request->getParam('image'), null, $image);
            $width = $set->file->new_width;
            $height = $set->file->new_height;
            $set->size = $set->file->filesize;
            $set->file = $get_file;
            
            $set->rand = md5(rand(1,100));
            $set->save();

            $set['width'] = $width;
            $set['height'] = $height;
            $set['path'] = $request->getParam('path').'/';

            return $response->withJson($set);
        } catch (\Exception $e) {
            return $response->withJson($e->getMessage(), 422);
        }

    }

    public function resize($request, $response) 
    {
        $data = json_decode($request->getParam('data'));
        if($data[4]->value < 200) {

            return $response->withJson('Нельзя делать изображение меньше 200px', 422);
        } elseif($data[4]->value > 4096) {

            return $response->withJson('Нельзя делать изображение больше 4096px', 422);
        } else {

            try {
                $set = $data[3]->value::where('id', $data[0]->value)->first();
                
                // Полная картина
                $set->file = new Uploader('file');
                $set->file->resize_width = (int)$data[4]->value;
                $set->file->rootUpload = $data[2]->value;
                $get_image = $set->file->resize(preg_replace('#\?id\=.*#ui', '', $data[1]->value), true);
                $width = $set->file->new_width;
                $height = $set->file->new_height;
                $set->size = $set->file->filesize;
                $set->file = $get_image;

                $set->rand = md5(rand(1, 100));
                $set->save();

                $set['width'] = $width;
                $set['height'] = $height;
                $set['path'] = $data[2]->value.'/';

                return $response->withJson($set);
            } catch (\Exception $e) {
                return $response->withJson($e->getMessage(), 422);
            }
        }
    }

    public function namealt($request, $response) 
    {
        $data = json_decode($request->getParam('data'));
        try {
            $set = $request->getParam('model')::find($data[0]->value);
            $set->title = $data[1]->value;
            if($request->getParam('type') == 'image') {
                $set->alt = $data[2]->value;
            }
            $set->save();

            return $response->withJson($set);

        } catch (\Exception $e) {
            return $response->withJson($e->getMessage(), 422);
        }
    }

    public function remove($request, $response) 
    {
        try {
            $set = $request->getParam('model')::find($request->getParam('id'));
            @unlink('/static/'.$request->getParam('path').'/'.$set->file);
            $set->delete();

            return $response->withJson('ok');

        } catch (\Exception $e) {
            return $response->withJson($e->getMessage(), 422);
        }
    }

    public function replace($request, $response)
    {
        try {
            $set = $request->getParam('model')::find($request->getParam('id'));
            $file = $set->file;

            // Полная картина
            $set->file = new Uploader('file');
            $set->file->minwidth = 20;
            $set->file->minheight = 20;
            $set->file->rootUpload = $request->getParam('path');
            $get_file = $set->file->filemanagerUpload($request->getParam('file'), null, $file);
            $set->file = $get_file;

            $set->type = $request->getParam('type');
            $set->size = $request->getParam('size');
            $set->rand = md5(rand(1,100));
            $set->save();

            return $response->withJson($set);

        } catch (\Exception $e) {
            return $response->withJson($e->getMessage(), 422);
        }
    }

    public function search($request, $response)
    {
        $data = json_decode($request->getParam('data'));
        $get = $request->getParam('model')::
                where('title', 'like', '%'.$data[0]->value.'%')
                ->where($request->getParam('conds'))
                ->orderBy('created_at', 'desc')
                ->limit(18)
                ->get();
        
        $this->view->render($response, 'filemanager/template/ajax.twig', [
            'get' => $get,
            'path' => $request->getParam('path')
        ]);
    }

    public function paginator($request, $response) 
    {
        $page = $request->getParam('page');
        $total = $request->getParam('total');
        $active = $request->getParam('active');
        
        $this->view->render($response, 'filemanager/template/paginator.twig', [
            'page' => $page,
            'total' => $total,
            'active' => $active,
        ]);
    }
    
    public function sendmail($request, $response) 
    {
        $validation = $this->validator->validate($request, [
            'email' => v::notEmpty(),
        ]);
        if($validation->failed()) {
            return $response->withJson('email empty', 422);
        }
        if($request->getParam('ids') === null) {
            return $response->withJson('no files selected', 422);
        }
        
        $files = [];
        foreach($request->getParam('model')::whereIn('id', $request->getParam('ids'))->get()->pluck('file') as $item) {
            $files[] = $_SERVER['DOCUMENT_ROOT'].'/static/'.$this->rootUpload.'/'.$item;
        }
        
        $message = 'Here are the auftrag files';
        \App\Support\Mails::send_mail($request->getParam('email'), $message, 'Auftrag files', $files);
        
        return $response->withJson('ok');
    }
    
    public function getcount($request, $response)
    {
        $query = $request->getParam('model')::where($request->getParam('conds'))
            ->orderBy('created_at', 'desc');

        if($request->getParam('insert_page') == 'id') {
            if($request->getParam('external_id') != 'false') {
                $query->where('external_id', $request->getParam('external_id'));
            } else {
                $query->whereNull('external_id');
            }
        }

        return $response->withJson([
            'ids' => $query->get()->pluck('id')->toArray(),
            'count' => $query->get()->count()
        ]);
    }
}