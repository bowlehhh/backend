<?php

namespace App\Filament\Auth\Pages;

use DanHarrin\LivewireRateLimiting\Exceptions\TooManyRequestsException;
use Filament\Auth\Http\Responses\Contracts\LoginResponse;
use Filament\Facades\Filament;
use Filament\Models\Contracts\FilamentUser;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Contracts\Auth\UserProvider;
use Illuminate\Validation\ValidationException;

class Login extends \Filament\Auth\Pages\Login
{
    public function authenticate(): ?LoginResponse
    {
        try {
            $this->rateLimit(5);
        } catch (TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();
        $credentials = $this->getCredentialsFromFormData($data);
        $remember = (bool) ($data['remember'] ?? false);
        $authGuard = Filament::auth();
        $authProvider = $authGuard->getProvider(); /** @var UserProvider $authProvider */

        $this->fireAttemptingEvent($authGuard, $credentials, $remember);

        $user = $authProvider->retrieveByCredentials($credentials);

        if (! $user) {
            $this->fireFailedEvent($authGuard, null, $credentials);
            $this->throwCustomFailure('data.email', 'Email tidak terdaftar.');
        }

        if (! $authProvider->validateCredentials($user, $credentials)) {
            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwCustomFailure('data.password', 'Password salah.');
        }

        if ($this->isInactive($user)) {
            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwCustomFailure('data.email', 'Akun tidak aktif.');
        }

        if (
            $user instanceof FilamentUser &&
            ! $user->canAccessPanel(Filament::getCurrentOrDefaultPanel())
        ) {
            // User kasir boleh login dari /admin/login, lalu diarahkan ke dashboard kasir.
            if (method_exists($user, 'isCashier') && $user->isCashier()) {
                $authGuard->login($user, $remember);
                session()->regenerate();
                session()->put('url.intended', url('/cashier/dashboard'));

                return app(LoginResponse::class);
            }

            $this->fireFailedEvent($authGuard, $user, $credentials);
            $this->throwCustomFailure('data.email', 'Akun ini tidak punya akses Admin Panel.');
        }

        $authGuard->login($user, $remember);
        session()->regenerate();

        return app(LoginResponse::class);
    }

    protected function throwCustomFailure(string $key, string $message): never
    {
        throw ValidationException::withMessages([
            $key => $message,
        ]);
    }

    protected function isInactive(Authenticatable $user): bool
    {
        if (method_exists($user, 'getAttribute')) {
            return ! (bool) $user->getAttribute('is_active');
        }

        return false;
    }
}

