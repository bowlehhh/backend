<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! $user->is_active) {
            abort(Response::HTTP_FORBIDDEN, 'Your account is inactive.');
        }

        $normalizedRoles = array_map(
            static fn (string $role): string => strtolower($role),
            $roles
        );
        $userRole = strtolower((string) $user->role);

        if ($normalizedRoles !== [] && ! in_array($userRole, $normalizedRoles, true)) {
            abort(Response::HTTP_FORBIDDEN, 'You are not authorized to access this resource.');
        }

        return $next($request);
    }
}
