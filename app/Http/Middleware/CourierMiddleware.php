<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CourierMiddleware
{
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check() && (auth()->user()->isCourier() || auth()->user()->isAdmin())) {
            return $next($request);
        }
        abort(403, 'Доступ запрещён. Требуется роль курьера.');
    }
}