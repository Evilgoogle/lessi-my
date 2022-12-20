<?php

namespace App\Models\Faq;

use Illuminate\Database\Eloquent\Model;

/**
 * @package App\Models
 */
class Article extends Model
{
    public    $timestamps   = false;
    public    $incrementing = true;
    protected $table        = 'faq_articles';
    protected $fillable     = [
        'title', 
        'description',
        'category_id'
    ];

    const TYPE_SELL    = 1;
    const TYPE_AUCTION = 2;

    const STATUS_ACTIVE = 1;
    const STATUS_FINISH = 2;

    public function isLikes()
    {
        return $this->hasOne(ArticleLike::class, 'article_id', 'id');
    }
    
    public function likes()
    {
        return $this->hasMany(ArticleLike::class, 'article_id', 'id')->where('liked', 1);
    }
    
    public function dislikes()
    {
        return $this->hasMany(ArticleLike::class, 'article_id', 'id')->where('liked', 0);
    }
    
    public function comments()
    {
        return $this->hasMany(ArticleComment::class, 'article_id', 'id');
    }
}
