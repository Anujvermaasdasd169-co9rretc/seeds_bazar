<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'out_for_delivery', 'delivered', 'cancelled', 'rto', 'returned'];

    protected $fillable = [
        'user_id', 'order_number', 'status', 'payment_status', 'payment_method',
        'payment_gateway', 'gateway_order_id', 'gateway_payment_id', 'gateway_signature',
        'paid_at', 'payment_failure_reason', 'stock_deducted_at',
        'guest_email', 'guest_phone',
        'shipping_name', 'shipping_phone', 'shipping_email', 'shipping_address',
        'shipping_line_1', 'shipping_line_2', 'shipping_landmark',
        'shipping_city', 'shipping_state', 'shipping_postal_code', 'shipping_country',
        'subtotal', 'shipping_method', 'delivery_estimate', 'discount', 'shipping_charge', 'tax', 'total',
    ];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2', 'discount' => 'decimal:2',
            'shipping_charge' => 'decimal:2', 'tax' => 'decimal:2', 'total' => 'decimal:2',
            'paid_at' => 'datetime',
            'stock_deducted_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function statusHistories(): HasMany
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }
}
