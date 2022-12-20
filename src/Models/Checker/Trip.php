<?php


namespace App\Models\Checker;


use Illuminate\Database\Eloquent\Model;

class Trip extends Model
{
    protected $table = 'checker_trip';
    protected $fillable = [
        'fin',
        'polkennzeichen',
        'kundenr',
        'crosses',
        'data',
        'return_data',
        'return_crosses',
        'is_finished',
        'km_stand',
        'return_km_stand',
    ];

    protected $casts = [
        'crosses' => 'array',
        'return_crosses' => 'array',
        'data' => 'array',
        'return_data' => 'array',
        'is_finished' => 'boolean',
    ];
}