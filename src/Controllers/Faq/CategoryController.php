<?php
namespace App\Controllers\Faq;

use App\Controllers\Controller;
use App\Models\Faq\Category;
use App\Models\Faq\Article;
use Respect\Validation\Validator as v;
use App\Models\Faq\SubCategory;

class CategoryController extends Controller
{
    public function index($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $items = Category::all();
        
        $this->view->render($response, 'faq/category/index.twig', ['items' => $items]);
    }

    public function add($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $this->view->render($response, 'faq/category/create.twig');
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
            Category::create([
                'title' => $request->getParam('title'),
            ]);
            
            $this->flash->addMessage('info', 'Category created');
            return $response->withRedirect($this->router->pathFor('faqs.category.index'));
        }
        $_SESSION['old'] = $request->getParams();
        
        return $response->withRedirect($this->router->pathFor('faqs.category.index'));
    }

    public function edit($request, $response, $id)
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }

        $category = Category::where('id', $id)->first();
        if(!isset($category)) {
            return $response->withStatus(404)->withHeader('Content-Type', 'text/html')->write('Category not found');
        }
        
        $sub_cats = SubCategory::where('category_id', $id)->get();
        
        $this->view->render($response, 'faq/category/update.twig', ['category' => $category, 'sub_cats' => $sub_cats]);
    }

    public function update($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $category = Category::where('id', $request->getParam('id'))->first();
        $validation = $this->validator->validate($request, [
            'title' => v::notEmpty(),
        ]);
        if (!$validation->failed()) {
            $category->update([
                'title' => $request->getParam('title'),
            ]);
            
            $this->flash->addMessage('info', 'Updated');
            return $response->withRedirect($this->router->pathFor('faqs.category.index'));
        }
        $_SESSION['old'] = $request->getParams();
        return $response->withRedirect('fag/categories/update-view/' . $request->getParam('id'));
    }

    public function delete($request, $response, $id)
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $del = ArticleCategory::where('id', $id)->first();
        $del->delete();
        
        return $response->withRedirect($this->router->pathFor('categories.page'));
    }
}
