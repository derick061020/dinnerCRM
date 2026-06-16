<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KitchenPurchase extends Model
{
    protected $fillable = [
        'date',
        'supplier',
        'item',
        'portions',
        'cost_total',
    ];

    protected $casts = [
        'date' => 'date',
        'portions' => 'integer',
        'cost_total' => 'decimal:2',
    ];

    public function getCostPerPortionAttribute(): float
    {
        return $this->portions > 0 ? (float) $this->cost_total / $this->portions : 0.0;
    }
}
