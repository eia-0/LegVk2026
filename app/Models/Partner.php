<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Partner extends Model
{
    protected $fillable = ['full_name', 'phone', 'email'];

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function soldItemsCount()
    {
        return OrderItem::whereHas('product', function ($query) {
            $query->where('partner_id', $this->id);
        })->whereHas('order', function ($query) {
            $query->where('status', '!=', 'cancelled');
        })->sum('quantity');
    }
}