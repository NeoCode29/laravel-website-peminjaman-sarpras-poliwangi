<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;

class PermissionMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, string $permission = null)
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if (!$permission) {
            $permission = optional($request->route())->getName();
        }

        try {
            if (!$permission || !$user->hasPermissionTo($permission)) {
                abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
            }
        } catch (PermissionDoesNotExist $e) {
            abort(403, 'Anda tidak memiliki izin untuk mengakses halaman ini.');
        }

        return $next($request);
    }
}

