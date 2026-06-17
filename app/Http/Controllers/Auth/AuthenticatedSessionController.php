<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Illuminate\Validation\ValidationException;
use Illuminate\Cache\RateLimiter;
use Illuminate\Support\Str;
use Carbon\Carbon;

class AuthenticatedSessionController extends Controller
{
    /**
     * Максимальное количество попыток для первого уровня.
     */
    protected function maxAttemptsFirstLevel(): int
    {
        return 5;
    }

    /**
     * Время блокировки для первого уровня (в секундах).
     */
    protected function decayFirstLevel(): int
    {
        return 5 * 60; // 5 минут
    }

    /**
     * Максимальное количество попыток для второго уровня.
     */
    protected function maxAttemptsSecondLevel(): int
    {
        return 5;
    }

    /**
     * Время блокировки для второго уровня.
     */
    protected function decaySecondLevel(): int
    {
        return 60 * 60; // 1 час
    }

    /**
     * Время блокировки для третьего уровня (одна попытка в период).
     */
    protected function decayThirdLevel(): int
    {
        return 10 * 24 * 60 * 60; // 10 дней
    }

    /**
     * Получить RateLimiter.
     */
    protected function limiter(): RateLimiter
    {
        return app(RateLimiter::class);
    }

    /**
     * Ключ для ограничения по email/IP.
     */
    protected function throttleKey(Request $request): string
    {
        return Str::transliterate(Str::lower($request->input('email')) . '|' . $request->ip());
    }

    /**
     * Проверка, не превышен ли лимит попыток.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    protected function ensureLoginIsNotThrottled(Request $request): void
    {
        $key = $this->throttleKey($request);
        $limiter = $this->limiter();

        // Проверяем с самого жёсткого уровня
        if ($limiter->tooManyAttempts($key . ':level3', 1)) {
            $seconds = $limiter->availableIn($key . ':level3');
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', ['seconds' => $seconds]),
            ]);
        }

        if ($limiter->tooManyAttempts($key . ':level2', $this->maxAttemptsSecondLevel())) {
            $seconds = $limiter->availableIn($key . ':level2');
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', ['seconds' => $seconds]),
            ]);
        }

        if ($limiter->tooManyAttempts($key . ':level1', $this->maxAttemptsFirstLevel())) {
            $seconds = $limiter->availableIn($key . ':level1');
            throw ValidationException::withMessages([
                'email' => trans('auth.throttle', ['seconds' => $seconds]),
            ]);
        }
    }

    /**
     * Увеличить счётчик неудачных попыток.
     */
    protected function incrementLoginAttempts(Request $request): void
    {
        $key = $this->throttleKey($request);
        $limiter = $this->limiter();

        // Общий счётчик для определения уровня (храним 30 дней)
        $limiter->hit($key, 60 * 24 * 30);

        $totalAttempts = $limiter->attempts($key);

        if ($totalAttempts <= $this->maxAttemptsFirstLevel()) {
            // Первый уровень
            $limiter->hit($key . ':level1', $this->decayFirstLevel());
        } elseif ($totalAttempts <= $this->maxAttemptsFirstLevel() + $this->maxAttemptsSecondLevel()) {
            // Второй уровень
            $limiter->hit($key . ':level2', $this->decaySecondLevel());
        } else {
            // Третий уровень: одна попытка, блокировка на 10 дней
            $limiter->hit($key . ':level3', $this->decayThirdLevel());
        }
    }

    /**
     * Очистить счётчики после успешного входа.
     */
    protected function clearLoginAttempts(Request $request): void
    {
        $key = $this->throttleKey($request);
        $limiter = $this->limiter();

        $limiter->clear($key);
        $limiter->clear($key . ':level1');
        $limiter->clear($key . ':level2');
        $limiter->clear($key . ':level3');
    }

    /**
     * Display the login view.
     */
    public function create(): View
    {
        return view('auth.login');
    }

    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
    {
        // Проверяем, не заблокирован ли вход
        $this->ensureLoginIsNotThrottled($request);

        try {
            // Стандартная аутентификация из LoginRequest (бросает исключение при провале)
            $request->authenticate();
        } catch (ValidationException $e) {
            // Увеличиваем счётчик неудач
            $this->incrementLoginAttempts($request);

            // Если причина – неверные учётные данные, можем добавить информацию о блокировке
            // Пробрасываем исключение дальше, Laravel сам покажет ошибку
            throw $e;
        }

        // Успешный вход – сбрасываем все счётчики
        $this->clearLoginAttempts($request);

        $request->session()->regenerate();

        // Редирект в зависимости от роли
        $user = Auth::user();
        if ($user->role === 'courier') {
            return redirect()->intended(route('courier.index'));
        }

        return redirect()->intended(route('home'));
    }

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): RedirectResponse
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect('/');
    }
}