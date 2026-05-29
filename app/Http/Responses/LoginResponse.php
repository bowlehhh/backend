<?php

namespace App\Http\Responses;

use App\Models\User;
use Filament\Auth\Http\Responses\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        $user = $request->user();

        if ($user instanceof User && $user->isCashier()) {
            return new RedirectResponse(url('/cashier/dashboard'));
        }

        return new RedirectResponse(url('/admin/admin-dashboard'));
    }
}
