<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    protected $fillable = [
        'product_id',
        'date',
        'start_time',
        'pax',
        'source',
        'external_id',
        'status'
    ];
    
    protected static function boot()
    {
        parent::boot();

        static::creating(function ($booking) {
            $booking->reference = Str::uuid();
        });
    }

    protected $casts = [
        'date' => 'date',
    ];

    public function product()
    {
        return $this->belongsTo(Product::class);
    }
}
