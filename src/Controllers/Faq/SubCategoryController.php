<?php
namespace App\Controllers\Faq;

use App\Controllers\Controller;
use App\Models\Faq\SubCategory;
use App\Models\Faq\Article;
use Respect\Validation\Validator as v;

class SubCategoryController extends Controller
{
    public function add($request, $response, $param)
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $category_id = $param['category_id'];
        
        $this->view->render($response, 'faq/subcategory/create.twig', ['category_id' => $category_id]);
    }

    public function create($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $validation = $this->validator->validate($request, [
            'title' => v::notEmpty(),
        ]);
        
        if (!$validation->failed()) {
            $set = new SubCategory();
            $set->title = $request->getParam('title');
            $set->category_id = (int)$request->getParam('category_id');
            $set->save();
            
            $this->flash->addMessage('info', 'Sub Category created');
            return $response->withRedirect('/faqs/categories/edit/'.(int)$request->getParam('category_id'));
        }
        $_SESSION['old'] = $request->getParams();
        
        return $response->withRedirect($this->router->pathFor('faqs.category.index'));
    }

    public function edit($request, $response, $param)
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }

        $category_id = $param['category_id'];
        $category = SubCategory::where('id', $param['id'])->first();
        if(!isset($category)) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('SubCategory not found');
        }
        
        $this->view->render($response, 'faq/subcategory/update.twig', ['category' => $category, 'category_id' => $category_id]);
    }

    public function update($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $validation = $this->validator->validate($request, [
            'title' => v::notEmpty(),
        ]);

        if (!$validation->failed()) {
            $set = SubCategory::where('id', $request->getParam('id'))->first();
            $set->title = $request->getParam('title');
            $set->category_id = (int)$request->getParam('category_id');
            $set->save();
            
            $this->flash->addMessage('info', 'Updated');
            return $response->withRedirect('/faqs/categories/edit/'.(int)$request->getParam('category_id'));
        }
        $_SESSION['old'] = $request->getParams();
        
        return $response->withRedirect($this->router->pathFor('faqs.category.index'));
    }

    public function delete($request, $response, $param)
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }

        $del = SubCategory::where('id', $param['id'])->first();
        $del->delete();
        
        return $response->withRedirect('/faqs/categories/edit/'.$param['category_id']);
    }
}
