<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Banner extends Model
{
    protected $fillable = ['image', 'interval', 'order', 'active'];

    protected $casts = [
        'active' => 'boolean',
    ];

    /**
     * Аксессор для получения полного URL изображения баннера.
     */
    public function getImageUrlAttribute()
    {
        return $this->image ? asset('storage/' . $this->image) : null;
    }
}