<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name',
        'description',
        'price',
        'image',
        'category_id',
        'preparation_time',
        'stock',
        'unlimited',
        'partner_id',
        'commission_percent',
        'payout_reset_at',
    ];

    protected $casts = [
        'unlimited' => 'boolean',
        'commission_percent' => 'integer',
        'payout_reset_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function partner()
    {
        return $this->belongsTo(Partner::class);
    }

    public function orderItems()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function isAvailable(int $quantity = 1): bool
    {
        if ($this->unlimited) return true;
        return $this->stock !== null && $this->stock >= $quantity;
    }

    public function decrementStock(int $quantity): void
    {
        if (!$this->unlimited && $this->stock !== null) {
            $this->decrement('stock', $quantity);
        }
    }

    /**
     * Продажи с момента последнего обнуления (payout_reset_at)
     */
    public function soldSinceLastPayout(): int
    {
        return $this->orderItems()
            ->whereHas('order', fn($q) => $q->where('status', '!=', 'cancelled'))
            ->when($this->payout_reset_at, function ($query) {
                $query->where('order_items.created_at', '>=', $this->payout_reset_at);
            })
            ->sum('quantity');
    }
}