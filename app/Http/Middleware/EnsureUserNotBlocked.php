<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class EnsureUserNotBlocked
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        \Log::info('EnsureUserNotBlocked middleware accessed', [
            'url' => $request->url(),
            'route' => $request->route() ? $request->route()->getName() : null,
            'method' => $request->method()
        ]);

        $user = $request->user();

        // Skip if user is not authenticated
        if (!$user) {
            \Log::info('EnsureUserNotBlocked: User not authenticated, skipping');
            return $next($request);
        }

        // Skip if user is on profile setup or related routes
        if ($request->routeIs('profile.setup') || 
            $request->routeIs('profile.complete-setup') ||
            $request->routeIs('profile.get-prodis') ||
            $request->is('setup*') ||
            $request->is('profile/setup*') ||
            $request->is('profile/*')) {
            \Log::info('EnsureUserNotBlocked: Profile setup route detected, skipping check');
            return $next($request);
        }

        if (method_exists($user, 'isBlocked') && $user->isBlocked()) {
            \Log::warning('EnsureUserNotBlocked: User is blocked, logging out', [
                'user_id' => $user->id,
                'username' => $user->username
            ]);
            auth()->logout();
            return redirect()->route('login')
                ->withErrors(['username' => 'Akun Anda diblokir. Silakan hubungi administrator.']);
        }

        \Log::info('EnsureUserNotBlocked: User is not blocked, continuing');
        return $next($request);
    }
}
