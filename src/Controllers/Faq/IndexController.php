<?php
namespace App\Controllers\Faq;

use App\Models\Faq\Article;
use App\Models\Faq\ArticleKeyword;
use App\Models\Faq\Category;
use App\Models\Faq\SubCategory;
use App\Controllers\Controller;
use Response;

class IndexController extends Controller
{
    public function index($request, $response) 
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $categories = Category::all();
        
        $this->view->render($response, 'faq/index.twig', ['categories' => $categories]);
    }
    
    public function category($request, $response, $args) 
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $category = Category::find($args['id']);
        
        $sub_categories = SubCategory::where('category_id', $category->id)->get();
        $articles = Article::where('category_id', $args['id'])->get();

        $this->view->render($response, 'faq/show.twig',['articles' => $articles, 'category' => $category, 'sub_categories' => $sub_categories]);
    }
    
    public function sub_category($request, $response, $args)
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $category = Category::find($args['cat_id']);
        
        $sub_categories = SubCategory::where('category_id', $args['cat_id'])->get();
        $articles = Article::where('sub_category_id', $args['id'])->get();
        
        $this->view->render($response, 'faq/show.twig',['articles' => $articles, 'category' => $category, 'sub_categories' => $sub_categories]);
    }
    
    public function search($request,$response)
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $string = strtolower($request->getParam('string') ? : '');

        $keywords = ArticleKeyword::where('keyword', 'LIKE', '%'.$string.'%')->get();
        $articles_key = Article::whereIn('id', $keywords->pluck('article_id'))->get();
        
        $articles_title = Article::where('title', 'LIKE', '%'.$string.'%')->whereNotIn('id', $articles_key->pluck('id'))->get();
        $articles = $articles_title->merge($articles_key);
        
        return $response->withJson($articles);
    }
    
    public function searchResult($request, $response)
    {
        if (!$this->Auth->user()->priviliges()->contains('faq.show')) {
            return $response->withRedirect($this->router->pathFor('errors.403'));
        }
        
        $article = Article::find($request->getParam('id'));
        
        if($article->sub_category_id != null) {
            $category = Category::find($article->category_id);
            
            $sub_categories = SubCategory::where('category_id', $category->id)->get();
            $articles = Article::where('sub_category_id', $article->sub_category_id)->get();
        } else {
            $category = Category::find($article->category_id);
        
            $sub_categories = SubCategory::where('category_id', $category->id)->get();
            $articles = Article::where('category_id', $article->category_id)->get();
        }
        
        $this->view->render($response, 'faq/show.twig',['articles' => $articles, 'category' => $category, 'sub_categories' => $sub_categories, 'open' => $article->id ]);
    }
}
