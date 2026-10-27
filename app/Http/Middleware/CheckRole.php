<?php

namespace App\Http\Middleware;

use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Pemakaian: ->middleware('role:superadmin') atau 'role:admin_bengkel,user'
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        if (! $user) {
            return redirect()->route('login');
        }

        $allowedIds = array_map(
            fn (string $role) => User::ROLE_MAP[$role] ?? null,
            $roles
        );

        abort_unless(
            in_array((int) $user->role_id, $allowedIds, true),
            403,
            'Anda tidak memiliki akses ke halaman ini.'
        );

        return $next($request);
    }
}
