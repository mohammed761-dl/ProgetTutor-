<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Session\TokenMismatchException;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class HandleAdminRequests
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Check if user is authenticated as admin
        if (! Auth::guard('admin')->check()) {
            if ($request->ajax() || $request->wantsJson()) {
                return response('Unauthorized.', 401);
            }

            return redirect()->route('serp-admin-login');
        }

        // For POST/PUT/DELETE requests, verify CSRF token
        if ($request->isMethod('POST') || $request->isMethod('PUT') ||
            $request->isMethod('PATCH') || $request->isMethod('DELETE')) {

            if (! $request->hasHeader('X-CSRF-TOKEN') && ! $request->input('_token')) {
                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json(['message' => 'CSRF token missing'], 419);
                }
                throw new TokenMismatchException('CSRF token missing');
            }
        }

        return $next($request);
    }
}
