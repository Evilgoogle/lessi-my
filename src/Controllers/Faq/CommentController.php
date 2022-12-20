<?php
namespace App\Controllers\Faq;

use App\Controllers\Faq\Libs\CategoryCreate;
use App\Models\Faq\Article;
use App\Models\Faq\ArticleKeyword;
use App\Models\Faq\ArticleComment;
use App\Models\Faq\Category;
use App\Models\Faq\SubCategory;
use App\Controllers\Controller;
use Respect\Validation\Validator as v;

class CommentController extends Controller
{   
    public function add($request, $response) 
    { 
        $template = $this->view->fetch('faq/comment/add.twig', ['article_id' => $request->getParam('article_id'), 'user_id' => $this->Auth->user()->id]);
        
        return $response->withJson($template);
    }

    public function create($request, $response)
    {
        $validation = $this->validator->validate($request, [
            //'name'       => v::notEmpty(),
            //'email'      => v::notEmpty(),
            'comment'    => v::notEmpty()
        ]);

        if (!$validation->failed()) {
            $set = new ArticleComment();
            $set->article_id = $request->getParam('article_id');
            $set->user_id = $request->getParam('user_id');
            //$set->name = $request->getParam('name');
            //$set->email = $request->getParam('email');
            $set->comment = $request->getParam('comment');
            $set->save();
            
            $template = $this->view->fetch('faq/comment/item.twig', ['comment' => $set]);
        
            return $response->withJson($template);
        } else {
            $errors = [];
            foreach($validation->getErrors() as $k=>$v) {
                $errors[$k] = $v[0];
            }
 
            return $response->withJson(json_encode($errors), 422);
        }
    }

    public function edit($request, $response, $args) 
    {
        $article_id = $args['article_id'];
        $comment = ArticleComment::find($args['id']);
        
        $this->view->render($response, 'faq/comment/edit.twig', ['comment' => $comment, 'article_id' => $article_id]);
    }

    public function update($request, $response) 
    {
        $validation = $this->validator->validate($request, [
            //'name'       => v::notEmpty(),
            //'email'      => v::notEmpty(),
            'comment'    => v::notEmpty()
        ]);

        if (!$validation->failed()) {
            $set = ArticleComment::find($request->getParam('id'));
            //$set->name = $request->getParam('name');
            //$set->email = $request->getParam('email');
            $set->comment = $request->getParam('comment');
            $set->save();
            
            return $response->withRedirect('/faqs/articles/edit/'.$request->getParam('article_id'));
        }
        
        $_SESSION['old'] = $request->getParams();
        return $response->withRedirect('/faqs/articles/comments/'.$request->getParam('article_id').'/edit/'.$request->getParam('id'));
    }
    
    public function delete($request, $response, $args) 
    {      
        $del = ArticleComment::find($args['id']);
        if(isset($del)) {
            $del->delete();
        }
        
        return $response->withRedirect('/faqs/articles/edit/'.$args['article_id']);
    }
};
