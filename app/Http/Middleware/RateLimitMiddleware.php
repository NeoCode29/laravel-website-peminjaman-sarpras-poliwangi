<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class RateLimitMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @param  string  $action
     * @param  int  $maxAttempts
     * @param  int  $decayMinutes
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next, string $action = 'general', int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $key = $action . '.' . $request->ip();
        
        if (RateLimiter::tooManyAttempts($key, $maxAttempts)) {
            $seconds = RateLimiter::availableIn($key);
            
            Log::warning('Rate limit exceeded', [
                'action' => $action,
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->url(),
                'seconds_remaining' => $seconds
            ]);
            
            if ($request->expectsJson()) {
                throw ValidationException::withMessages([
                    'rate_limit' => "Terlalu banyak permintaan. Coba lagi dalam " . ceil($seconds / 60) . " menit."
                ]);
            }
            
            return redirect()->back()
                ->withErrors(['rate_limit' => "Terlalu banyak permintaan. Coba lagi dalam " . ceil($seconds / 60) . " menit."]);
        }
        
        RateLimiter::hit($key, $decayMinutes * 60);
        
        return $next($request);
    }
}
