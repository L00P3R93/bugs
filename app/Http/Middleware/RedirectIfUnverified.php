<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class RedirectIfUnverified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // If user is authenticated but not verified, redirect to verification notice
        if ($user && ! $user->hasVerifiedEmail()) {
            // Allow access to public auth routes
            $publicRoutes = [
                'login',
                'register',
                'verification.notice',
                'verification.verify',
                'password.request',
                'password.email',
                'password.reset',
                'logout',
                'home',
            ];

            // Don't redirect if accessing public auth routes or verification-related routes
            if (in_array($request->route()->getName(), $publicRoutes) ||
                str_starts_with($request->path(), 'email/') ||
                str_starts_with($request->path(), 'password/') ||
                $request->path() === '/') {
                return $next($request);
            }

            // Redirect to verification notice for all other routes
            return Redirect::route('verification.notice');
        }

        return $next($request);
    }
}
