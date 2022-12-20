<?php

namespace App\Models\Faq;

use Illuminate\Database\Eloquent\Model;

/**
 * @package App\Models
 */
class ArticleKeyword extends Model
{
    public    $timestamps   = false;
    public    $incrementing = true;
    protected $table        = 'faq_article_keywords';
    protected $fillable     = [
        'id', 
        'article_id', 
        'keyword'
    ];

    const TYPE_SELL    = 1;
    const TYPE_AUCTION = 2;

    const STATUS_ACTIVE = 1;
    const STATUS_FINISH = 2;

    public function article()
    {
        return $this->hasMany(Article::class, 'id', 'article_id');
    }
}
