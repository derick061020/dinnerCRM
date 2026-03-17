<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\Product;

class ProductTimeslot extends Model
{
    protected $fillable = [
        'product_id',
        'weekday',
        'start_time',
        'priority',
        'active'
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}