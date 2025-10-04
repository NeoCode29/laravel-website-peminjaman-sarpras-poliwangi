<?php

namespace App\Http\Middleware;

use Illuminate\Auth\Middleware\Authenticate as Middleware;

class Authenticate extends Middleware
{
    /**
     * Get the path the user should be redirected to when they are not authenticated.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return string|null
     */
    protected function redirectTo($request)
    {
        \Log::warning('Authenticate middleware: User not authenticated, redirecting to login', [
            'url' => $request->url(),
            'route' => $request->route() ? $request->route()->getName() : null,
            'method' => $request->method(),
            'user_authenticated' => auth()->check(),
            'session_id' => session()->getId()
        ]);
        
        if (! $request->expectsJson()) {
            return route('login');
        }
    }
}
