<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;

class EnsureProfileCompleted
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
        // Skip middleware entirely if database is not available
        try {
            $user = $request->user();
        } catch (\Exception $e) {
            // If database connection fails, skip middleware
            return $next($request);
        }

        // Skip if user is not authenticated
        if (!$user) {
            return $next($request);
        }

        // Skip if user is already on profile setup page or related routes
        if ($request->routeIs('profile.setup') || 
            $request->routeIs('profile.complete-setup') ||
            $request->routeIs('profile.get-prodis') ||
            $request->routeIs('login') ||
            $request->routeIs('auth.*') ||
            $request->routeIs('oauth.*') ||
            $request->is('login') ||
            $request->is('oauth/*') ||
            $request->is('setup*') ||
            $request->is('profile/setup*') ||
            $request->is('/') ||
            $request->is('logout')) {
            return $next($request);
        }

        // Check if profile is completed (with error handling)
        try {
            // Semua user termasuk admin harus setup profile
            if (method_exists($user, 'isProfileCompleted') && !$user->isProfileCompleted()) {
                return redirect()->route('profile.setup')
                    ->with('warning', 'Silakan lengkapi profil Anda terlebih dahulu sebelum melanjutkan.');
            }
        } catch (\Exception $e) {
            // If profile check fails, allow request to continue
            return $next($request);
        }

        return $next($request);
    }
}