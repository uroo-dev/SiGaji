<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserHasRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();
        if (! $user) {
            return redirect()->route('login');
        }

        $userRoleValue = $user->role instanceof UserRole ? $user->role->value : (string) $user->role;

        if (empty($roles)) {
            $currentRoute = $request->route();
            $action = $currentRoute ? $currentRoute->getAction() : [];
            $middlewareList = $action['middleware'] ?? [];
            foreach ($middlewareList as $m) {
                if ($m === 'role.admin') $roles[] = 'admin';
                if ($m === 'role.supervisor') $roles[] = 'supervisor';
                if ($m === 'role.employee') $roles[] = 'employee';
            }
        }

        if (empty($roles)) {
            $roles = ['admin'];
        }

        if (! in_array($userRoleValue, $roles, true)) {
            abort(403, 'Akses ditolak. Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}
