<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class LogUserActivity
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
        if (Auth::check()) {
            $user = Auth::user();
            
            // Update last activity
            if (method_exists($user, 'updateLastActivity')) {
                $user->updateLastActivity();
            }
            
            // Log activity for audit purposes
            Log::info('User activity', [
                'user_id' => $user->id,
                'username' => $user->username,
                'url' => $request->url(),
                'method' => $request->method(),
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);
        }

        return $next($request);
    }
}

