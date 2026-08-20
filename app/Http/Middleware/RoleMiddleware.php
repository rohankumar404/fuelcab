<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use App\Enums\UserRole;
use App\Exceptions\ForbiddenException;
use App\Exceptions\UnauthorizedException;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class RoleMiddleware
{
    /**
     * Handle an incoming request.
     *
     * Usage in routes: ->middleware('role:super_admin,operations_team')
     *
     * Checks the user's role_type enum (direct DB column) and also
     * falls back to Spatie's hasAnyRole() for permission-based roles.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            throw new UnauthorizedException;
        }

        // Check against role_type enum (primary source of truth)
        $allowedRoles = array_map(
            fn (string $r) => UserRole::from($r),
            $roles
        );

        $userRoleType = $user->role_type;

        // Match on role_type enum OR Spatie hasAnyRole()
        if (
            ! in_array($userRoleType, $allowedRoles, true)
            && ! $user->hasAnyRole($roles)
        ) {
            throw new ForbiddenException('You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
