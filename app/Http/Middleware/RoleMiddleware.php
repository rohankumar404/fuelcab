<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Exceptions\UnauthorizedException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes: ->middleware('role:admin,vendor')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            throw new UnauthorizedException();
        }

        $allowedRoles = array_map(
            fn (string $r) => UserRole::from($r),
            $roles
        );

        if (! in_array($user->role, $allowedRoles)) {
            throw new UnauthorizedException('You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
