<?php

namespace App\Models\Faq;

use Illuminate\Database\Eloquent\Model;

/**
 * @package App\Models
 */
class ArticleLike extends Model
{
    public    $timestamps   = false;
    public    $incrementing = true;
    protected $table        = 'faq_article_likes';
    protected $fillable     = [
        'id', 
        'user_id', 
        'article_id', 
        'liked'
    ];

    const TYPE_SELL    = 1;
    const TYPE_AUCTION = 2;

    const STATUS_ACTIVE = 1;
    const STATUS_FINISH = 2;
}
