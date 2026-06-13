<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Characteristic extends Model
{
    protected $fillable = ['name', 'color', 'icon', 'order'];

    public function products()
    {
        return $this->belongsToMany(Product::class);
    }
}