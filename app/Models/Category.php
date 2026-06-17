<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    protected $fillable = ['name', 'parent_id', 'image', 'show_in_catalog', 'show_in_ready_eat', 'order'];

    protected $casts = [
        'show_in_catalog' => 'boolean',
        'show_in_ready_eat' => 'boolean',
    ];

    // скоуп для сортировки по order
    public function scopeOrdered($query)
    {
        return $query->orderBy('order');
    }

    public function parent()
    {
        return $this->belongsTo(Category::class, 'parent_id');
    }

    // дочерние с сортировкой по order
    public function children()
    {
        return $this->hasMany(Category::class, 'parent_id')->ordered();
    }

    public function products()
    {
        return $this->hasMany(Product::class);
    }

    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}