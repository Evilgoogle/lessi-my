<?php
namespace App\Models\Faq;

use Illuminate\Database\Eloquent\Model;

/**
 * @package App\Models
 */
class Category extends Model
{
    public    $timestamps   = false;
    public    $incrementing = true;
    protected $table        = 'faq_categories';
    protected $fillable     = [
        'id',
        'title'
    ];

    const TYPE_SELL    = 1;
    const TYPE_AUCTION = 2;

    const STATUS_ACTIVE = 1;
    const STATUS_FINISH = 2;
}
