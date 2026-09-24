<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next, $permission = null, $guard = null): Response
    {
        $authGuard = Auth::guard($guard ?: 'admin');

        if ($authGuard->guest()) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthenticated.'], 401);
            }
            return redirect()->route('login');
        }

        $user = $authGuard->user();

        // 1. Super Admin bypass
        if (method_exists($user, 'isSuperAdmin') && $user->isSuperAdmin()) {
            return $next($request);
        }

        if (method_exists($user, 'hasRole') && ($user->hasRole('admin') || $user->hasRole('super-admin') || $user->hasRole('Super Admin') || $user->hasRole('Administrator'))) {
            return $next($request);
        }

        // 2. Resolve permission from route name if not explicitly passed
        if (is_null($permission)) {
            $permission = $request->route()?->getName();
        }

        // If route has no name (closure or unnamed route), allow request through
        if (empty($permission)) {
            return $next($request);
        }

        // 3. Split piped permissions
        $permissions = is_array($permission) ? $permission : explode('|', $permission);

        // 4. Excluded universal routes
        $universalRoutes = ['login', 'logout', 'dashboard', 'profile.show', 'profile.update', 'profile.password'];
        foreach ($permissions as $p) {
            if (in_array($p, $universalRoutes, true)) {
                return $next($request);
            }
        }

        // 5. Check user authorization
        foreach ($permissions as $p) {
            $p = trim($p);
            if ($user->can($p)) {
                return $next($request);
            }
            if (method_exists($user, 'hasPermission') && $user->hasPermission($p)) {
                return $next($request);
            }
        }

        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => 'Anda tidak memiliki hak akses untuk tindakan ini.',
                'required_permission' => $permission,
            ], 403);
        }

        abort(403, 'Anda tidak memiliki hak akses (permission) untuk membuka halaman atau melakukan aksi ini.');
    }
}
