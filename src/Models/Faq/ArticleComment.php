<?php

namespace App\Models\Faq;

use Illuminate\Database\Eloquent\Model;
use App\Models\UserManager\User;

/**
 * @package App\Models
 */
class ArticleComment extends Model
{
    public    $timestamps   = false;
    public    $incrementing = true;
    protected $table        = 'faq_article_comments';
    protected $fillable     = [
        'id', 
        'user_id', 
        'article_id', 
        'name',
        'email',
        'comment'
    ];

    const TYPE_SELL    = 1;
    const TYPE_AUCTION = 2;

    const STATUS_ACTIVE = 1;
    const STATUS_FINISH = 2;
    
    public function getUserData() 
    {
        return User::find($this->user_id);
    }
}
