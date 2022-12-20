<?php
namespace App\Controllers\Faq;

use App\Controllers\Faq\Libs\CategoryCreate;
use App\Models\Faq\Article;
use App\Models\Faq\ArticleKeyword;
use App\Models\Faq\ArticleComment;
use App\Models\Faq\ArticleLike;
use App\Models\Faq\Category;
use App\Models\Faq\SubCategory;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;

class ArticleController extends Controller
{
    public function index($request, $response) 
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $articles = Article::all();
        $this->view->render($response, 'faq/article/index.twig', ['articles'=> $articles]);
    }

    public function add($request, $response) 
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $all_cats = [];
        foreach(Category::all() as $cat) {
            $all_cats[] = (object)[
                'id' => $cat->id,
                'title' => $cat->title,
                'parent' => 0
            ];
            foreach(SubCategory::all() as $subcat) {
                if($cat->id == $subcat->category_id) {
                    $all_cats[] = (object)[
                        'id' => $subcat->id,
                        'title' => $subcat->title,
                        'parent' => $cat->id
                    ];
                }
            }
        }
        $category = (new CategoryCreate())->catCreateAdmin($all_cats);
        
        $this->view->render($response, 'faq/article/create.twig', ['category' => $category]);
    }

    public function create($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        $validation = $this->validator->validate($request, [
            'title'       => v::notEmpty(),
            'description' => v::notEmpty(),
            'keywords'    => v::notEmpty(),
            'category'    => v::notEmpty(),
        ]);
   
        if (!$validation->failed()) {
            $set = new Article();
            $set->title = $request->getParam('title');
            $set->description = $request->getParam('description');
            
            $category_id = json_decode($request->getParam('category'))[0];
            $sub_category_id = json_decode($request->getParam('category'))[1];
            if($category_id == 0) {
                $set->category_id = json_decode($request->getParam('category'))[1];
            } else {
                $set->category_id = json_decode($request->getParam('category'))[0];
                $set->sub_category_id = json_decode($request->getParam('category'))[1];
            }
            $set->save();
            
            ArticleKeyword::create([
                'article_id' => $set->id,
                'keyword'    => $request->getParam('keywords'),
            ]);
            
            $this->flash->addMessage('info', 'Article created');
            return $response->withRedirect($this->router->pathFor('faqs.article.page'));
        }
        
        $_SESSION['old'] = $request->getParams();
        return $response->withRedirect($this->router->pathFor('faqs.article.add'));
    }

    public function edit($request, $response, $id) 
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $article = Article::where('id',$id)->first();
        
        $all_cats = [];
        foreach(Category::all() as $cat) {
            $all_cats[] = (object)[
                'id' => $cat->id,
                'title' => $cat->title,
                'parent' => 0
            ];
            foreach(SubCategory::all() as $subcat) {
                if($cat->id == $subcat->category_id) {
                    $all_cats[] = (object)[
                        'id' => $subcat->id,
                        'title' => $subcat->title,
                        'parent' => $cat->id
                    ];
                }
            }
        }
        $category = (new CategoryCreate())->catCreateAdmin($all_cats, $article->category_id);
        
        $keywords = [];
        $keys = ArticleKeyword::where('article_id', $article->id)->first();
        if(!empty($keys)) {
            $keywords = explode(',', $keys->keyword);
        }
        
        $comments = ArticleComment::where('article_id', $article->id)->get();
        
        $this->view->render($response, 'faq/article/update.twig',['article' => $article, 'keywords' => $keywords, 'category' => $category, 'comments' => $comments]);
    }

    public function update($request, $response) 
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $article = Article::where('id', $request->getParam('id'))->first();
        $keywords = ArticleKeyword::where('article_id', $article->id)->first();
        if(!isset($keywords)) {
            $keywords = new ArticleKeyword();
            $keywords->article_id = $article->id;
        }
        
        $validation = $this->validator->validate($request, [
            'title'       => v::notEmpty(),
            'description' => v::notEmpty(),
            'keywords'    => v::notEmpty(),
            'category'    => v::notEmpty(),
        ]);
       
        if (!$validation->failed()) {
            $article->title = $request->getParam('title');
            $article->description = $request->getParam('description');
            $article->save();
            
            $keywords->keyword = $request->getParam('keywords');
            $keywords->save();
            
            $this->flash->addMessage('info', 'Updated created');
            return $response->withRedirect($this->router->pathFor('faqs.article.page'));
        }

        $_SESSION['old'] = $request->getParams();
        return $response->withRedirect('/faqs/articles/edit/'.$request->getParam('id'));
    }
    
    public function delete($request, $response, $id) 
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.manager')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $article = Article::where('id',$id)->first();
        $article->delete();
        
        return $response->withRedirect($this->router->pathFor('faqs.article.page'));
    }
    
    public function isLike($request, $response) 
    {
        $output = 0;
        $get = ArticleLike::where('article_id', $request->getParam('article_id'))->first();

        if(isset($get)) {
            $get->update([
                'liked' => $request->getParam('is_liked')
            ]);
            $output = $get->liked;
        } else {
            $set = ArticleLike::create([
                'user_id' => $this->Auth->user()->id,
                'article_id' => $request->getParam('article_id'),
                'liked' => $request->getParam('is_liked')
            ]);
            $output = $set->liked;
        }
        
        return $output;
    }
};
