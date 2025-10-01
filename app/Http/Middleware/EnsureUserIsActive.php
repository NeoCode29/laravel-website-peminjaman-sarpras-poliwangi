<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureUserIsActive
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Skip if user is on profile setup or related routes
            if ($request->routeIs('profile.setup') || 
                $request->routeIs('profile.complete-setup') ||
                $request->routeIs('profile.get-prodis') ||
                $request->is('setup*') ||
                $request->is('profile/setup*') ||
                $request->is('profile/*')) {
                return $next($request);
            }
            
            // Check if user is blocked or inactive
            if (!$user->canLogin()) {
                \Log::warning('User blocked or inactive', [
                    'user_id' => $user->id,
                    'username' => $user->username,
                    'status' => $user->status,
                    'isActive' => $user->isActive(),
                    'isBlocked' => $user->isBlocked()
                ]);
                
                auth()->logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                
                return redirect()->route('login')
                    ->with('error', 'Akun Anda tidak aktif atau diblokir. Silakan hubungi administrator.');
            }
        }

        return $next($request);
    }
}
