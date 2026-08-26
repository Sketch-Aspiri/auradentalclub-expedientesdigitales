<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware genérico de rol para el andamiaje del Sprint 0 — las policies de dominio
 * (por módulo clínico) llegan en los sprints siguientes, ver CLAUDE.md §3 y §5.
 */
class EnsureUserHasRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user || ! in_array($user->role, array_map(fn (string $role) => UserRole::from($role), $roles), true)) {
            abort(403);
        }

        return $next($request);
    }
}
