<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Model;

class Review extends Model
{
    protected $fillable = [
        'product_id',
        'name',
        'rating',
        'comment',
    ];

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}