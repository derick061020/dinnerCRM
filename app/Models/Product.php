<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\ProductTimeslot;
use App\Models\Inventory;

class Product extends Model
{
    protected $fillable = [
        'name',
        'default_capacity',
        'wordpress_product_id'
    ];

    public function timeslots()
    {
        return $this->hasMany(ProductTimeslot::class);
    }

    public function inventories()
    {
        return $this->hasMany(Inventory::class);
    }
}
