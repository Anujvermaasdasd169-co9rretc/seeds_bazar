<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    public const STATUSES = ['pending', 'confirmed', 'processing', 'shipped', 'delivered', 'cancelled'];

    protected $fillable = [
        'user_id', 'order_number', 'status', 'payment_status', 'payment_method',
        'payment_gateway', 'gateway_order_id', 'gateway_payment_id', 'gateway_signature',
        'paid_at', 'payment_failure_reason', 'stock_deducted_at',
        'shipping_name', 'shipping_phone', 'shipping_address', 'subtotal',
        'shipping_method', 'delivery_estimate', 'discount', 'shipping_charge', 'tax', 'total',
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
}
