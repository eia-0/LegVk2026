<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    protected $fillable = [
        'name', 'description', 'price', 'image', 'category_id',
        'preparation_time', 'stock', 'unlimited',
    ];

    protected $casts = [
        'unlimited' => 'boolean',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    // Проверка доступности
    public function isAvailable(int $quantity = 1): bool
    {
        if ($this->unlimited) return true;
        return $this->stock !== null && $this->stock >= $quantity;
    }

    // Уменьшение стока
    public function decrementStock(int $quantity): void
    {
        if (!$this->unlimited && $this->stock !== null) {
            $this->decrement('stock', $quantity);
        }
    }
}