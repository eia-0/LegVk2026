<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    protected $fillable = [
        'user_id',
        'delivery_type',
        'delivery_address_id',
        'phone',
        'callback_needed',
        'payment_method',
        'delivery_date',
        'delivery_time',
        'status',
        'cancellation_reason',
        'courier_id',          // ← обязательно!
        'admin_seen',
        'total',
    ];

    protected $casts = [
        'callback_needed' => 'boolean',
        'admin_seen'       => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function deliveryAddress()
    {
        return $this->belongsTo(DeliveryAddress::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }

    public function courier()
    {
        return $this->belongsTo(User::class, 'courier_id');
    }

    // Русские названия статусов
    public function getStatusRuAttribute(): string
    {
        if ($this->status === 'cancelled') {
            return 'Отменён';
        }

        return match ($this->delivery_type) {
            'pickup' => match ($this->status) {
                'new'              => 'Новый',
                'accepted_cooking' => 'Принят и готовится',
                'ready_for_pickup' => 'Ожидает получения',
                'completed'        => 'Выполнен',
                default            => $this->status,
            },
            'delivery' => match ($this->status) {
                'new'                => 'Новый',
                'accepted_cooking'   => 'Принят и готовится',
                'ready_for_delivery' => 'Ожидает доставки',
                'delivering'         => 'Доставляется',
                'completed'          => 'Выполнен',
                default              => $this->status,
            },
            default => $this->status,
        };
    }

    public function getStatusColorAttribute(): string
    {
        return match ($this->status) {
            'new'                                => 'bg-blue-500',
            'accepted_cooking'                   => 'bg-yellow-500',
            'ready_for_pickup', 'ready_for_delivery' => 'bg-purple-500',
            'delivering'                         => 'bg-orange-500',
            'completed'                          => 'bg-green-500',
            'cancelled'                          => 'bg-red-500',
            default                              => 'bg-gray-500',
        };
    }
}