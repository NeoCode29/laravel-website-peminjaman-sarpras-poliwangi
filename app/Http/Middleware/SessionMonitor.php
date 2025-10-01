<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class SessionMonitor
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
        $sessionId = session()->getId();
        $isAuthenticated = auth()->check();
        
        // Log session information for debugging
        Log::debug('Session Monitor', [
            'session_id' => $sessionId,
            'is_authenticated' => $isAuthenticated,
            'user_id' => $isAuthenticated ? auth()->id() : null,
            'url' => $request->url(),
            'method' => $request->method(),
            'ip' => $request->ip(),
            'user_agent' => $request->userAgent()
        ]);
        
        // Check if session is valid
        if ($isAuthenticated && !$sessionId) {
            Log::warning('Authenticated user without session ID', [
                'user_id' => auth()->id(),
                'url' => $request->url()
            ]);
        }
        
        $response = $next($request);
        
        // Log session after processing
        Log::debug('Session Monitor - After', [
            'session_id' => session()->getId(),
            'is_authenticated' => auth()->check(),
            'user_id' => auth()->check() ? auth()->id() : null,
            'response_status' => $response->getStatusCode()
        ]);
        
        return $response;
    }
}

