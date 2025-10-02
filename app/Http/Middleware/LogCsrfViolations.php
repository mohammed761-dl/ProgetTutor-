<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class LogCsrfViolations
{
    /**
     * Handle an incoming request and log CSRF violations.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Check if the response status is 419 (CSRF token mismatch)
        if ($response->getStatusCode() === 419) {
            Log::warning('CSRF token mismatch detected', [
                'ip' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
                'user_id' => $request->user()?->id_user ?? null,
                'admin_id' => $request->user('admin')?->id ?? null,
                'timestamp' => now(),
                'headers' => [
                    'referer' => $request->header('referer'),
                    'x-forwarded-for' => $request->header('x-forwarded-for'),
                ],
            ]);
        }

        return $response;
    }
}
