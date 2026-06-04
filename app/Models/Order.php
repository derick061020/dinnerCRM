<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'woocommerce_order_id',
        'product_id',
        'customer_name',
        'customer_email',
        'total',
        'status',
        'booking_start',
        'booking_end',
        'data',
        'billing_first_name',
        'billing_last_name',
        'billing_phone',
        'billing_email',
        'billing_address_1',
        'billing_city',
        'billing_state',
        'billing_postcode',
        'billing_country',
        'payment_method',
        'transaction_id',
        'customer_note',
        'internal_notes',
        'quantity',
        'unit_price',
        'currency',
        'version',
        'prices_include_tax',
        'discount_total',
        'discount_tax',
        'shipping_total',
        'shipping_tax',
        'cart_tax',
        'total_tax',
        'customer_id',
        'order_key',
        'billing_company',
        'billing_address_2',
        'shipping_first_name',
        'shipping_last_name',
        'shipping_company',
        'shipping_address_1',
        'shipping_address_2',
        'shipping_city',
        'shipping_state',
        'shipping_postcode',
        'shipping_country',
        'shipping_phone',
        'payment_method_title',
        'customer_ip_address',
        'customer_user_agent',
        'created_via',
        'cart_hash',
        'order_number',
        'date_created',
        'date_modified',
        'date_completed',
        'date_paid',
        'subtotal',
        'subtotal_tax',
    ];

    protected $casts = [
        'total' => 'decimal:2',
        'woocommerce_order_id' => 'integer',
        'product_id' => 'integer',
        'quantity' => 'integer',
        'unit_price' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'booking_start' => 'datetime',
        'booking_end' => 'datetime',
        'data' => 'collection',
        'prices_include_tax' => 'boolean',
        'discount_total' => 'decimal:2',
        'discount_tax' => 'decimal:2',
        'shipping_total' => 'decimal:2',
        'shipping_tax' => 'decimal:2',
        'cart_tax' => 'decimal:2',
        'total_tax' => 'decimal:2',
        'customer_id' => 'integer',
        'subtotal' => 'decimal:2',
        'subtotal_tax' => 'decimal:2',
        'date_created' => 'datetime',
        'date_modified' => 'datetime',
        'date_completed' => 'datetime',
        'date_paid' => 'datetime',
    ];

    /**
     * Get the order items for this order.
     */
    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * Get the product associated with this order.
     */
    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class, 'product_id', 'wordpress_product_id');
    }

    /**
     * Scope to get orders by status.
     */
    public function scopeByStatus($query, $status)
    {
        return $query->where('status', $status);
    }

    /**
     * Scope to get pending orders.
     */
    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    /**
     * Scope to get completed orders.
     */
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    /**
     * Check if order is completed.
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if order is pending.
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Mark order as completed.
     */
    public function markAsCompleted(): bool
    {
        return $this->update(['status' => 'completed']);
    }

    /**
     * Get formatted total amount.
     */
    public function getFormattedTotalAttribute(): string
    {
        return '$' . number_format($this->total, 2);
    }
}
