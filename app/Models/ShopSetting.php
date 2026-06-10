<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ShopSetting extends Model
{
    protected $fillable = [
        'delivery_enabled',
        'delivery_cost',
        'free_delivery_from',
        'min_order_amount',
        'opening_hours',
        'pickup_address',
        'pickup_entrance',
        'pickup_latitude',
        'pickup_longitude',
        'phone',
    ];

    protected $casts = [
        'delivery_enabled' => 'boolean',
    ];

    public static function getSettings()
    {
        return cache()->rememberForever('shop_settings', function () {
            return self::firstOrFail();
        });
    }
}