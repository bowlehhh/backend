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

        if ($user instanceof User && $user->isAdminBesar()) {
            return new RedirectResponse(url('/admin/admin-besar'));
        }

        return new RedirectResponse(url('/admin/admin-dashboard'));
    }
}
