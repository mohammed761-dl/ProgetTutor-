<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;

class VerifyCsrfToken extends Middleware
{
    /**
     * The URIs that should be excluded from CSRF verification.
     *
     * @var array<int, string>
     */
    protected $except = [
        // Only exclude API routes from CSRF protection
        'api/*',
        // Exclude webhook endpoints if you have any
        'webhooks/*',
    ];

    /**
     * Determine if the request should be excluded from CSRF verification.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return bool
     */
    protected function shouldPassThrough($request)
    {
        // Always exclude API routes
        if ($request->is('api/*')) {
            return true;
        }

        return parent::shouldPassThrough($request);
    }
}
