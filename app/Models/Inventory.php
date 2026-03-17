<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Inventory extends Model
{
    protected $fillable = [
        'product_id',
        'date',
        'start_time',
        'capacity_total',
        'capacity_used'
    ];

    protected $casts = [
        'date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }

    public function getAvailableAttribute()
    {
        return $this->capacity_total - $this->capacity_used;
    }
    public function bookings()
    {
        return $this->hasMany(Booking::class,
            ['product_id','date','start_time'],
            ['product_id','date','start_time']
        );
    }
}
