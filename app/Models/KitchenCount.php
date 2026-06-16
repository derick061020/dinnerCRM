<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenCount extends Model
{
    protected $fillable = [
        'week_start',
        'item',
        'physical_count',
    ];

    protected $casts = [
        'week_start' => 'date',
        'physical_count' => 'integer',
    ];
}
