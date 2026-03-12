<?php

namespace App\Http\Middleware;

use Closure;
use Filament\Facades\Filament;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Symfony\Component\HttpFoundation\Response;

class EnsureEmailVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! $request->user() || ! $request->user()->hasVerifiedEmail()) {
            $currentPanel = Filament::getCurrentPanel();
            if ($currentPanel && ($request->is($currentPanel->getPath().'*') || str_starts_with($request->path(), 'admin'))) {
                return Redirect::route('verification.notice')
                    ->with('message', 'You must verify your email address before accessing any panels.');
            }

            // For API routes, return JSON response
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Your email address is not verified.',
                    'verification_required' => true,
                ], 403);
            }

            // For web routes, redirect to verification notice
            return Redirect::route('verification.notice');
        }

        return $next($request);
    }
}
