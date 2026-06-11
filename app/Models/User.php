<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
        'is_admin',
        'role',
        'phone',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
    ];

    public function cartItems()
    {
        return $this->hasMany(CartItem::class);
    }

    public function orders()
    {
        return $this->hasMany(Order::class);
    }

    public function deliveryAddresses()
    {
        return $this->hasMany(DeliveryAddress::class);
    }

    /**
     * Заказы, которые доставляет курьер
     */
    public function courierOrders()
    {
        return $this->hasMany(Order::class, 'courier_id');
    }

    /**
     * Является ли пользователь администратором
     */
    public function isAdmin(): bool
    {
        return $this->role === 'admin' || $this->is_admin;
    }

    /**
     * Является ли пользователь курьером
     */
    public function isCourier(): bool
    {
        return $this->role === 'courier';
    }
}