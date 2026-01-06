<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ForceCors
{
    /**
     * Handle an incoming request and always add CORS headers based on env.
     */
    public function handle(Request $request, Closure $next)
    {
        $allowed = env('CORS_ALLOWED_ORIGINS', '*');
        $allowCredentials = filter_var(env('CORS_ALLOW_CREDENTIALS', false), FILTER_VALIDATE_BOOLEAN);

        $origins = array_filter(array_map('trim', explode(',', $allowed)));
        $origin = $request->headers->get('Origin');

        // Determine which origin to set (must not set '*' when credentials are true)
        $setOrigin = null;
        if ($origin) {
            if (in_array('*', $origins, true)) {
                // wildcard allowed
                $setOrigin = $allowCredentials ? $origin : '*';
            } else {
                // check exact match (host maybe with scheme)
                foreach ($origins as $o) {
                    if ($o === $origin || preg_match('#^' . preg_quote($o, '#') . '$#i', $origin)) {
                        $setOrigin = $origin;
                        break;
                    }
                }
            }
        } else {
            // no Origin header; use first allowed if wildcard or present
            if (in_array('*', $origins, true)) {
                $setOrigin = '*';
            } elseif (!empty($origins)) {
                $setOrigin = $origins[0];
            }
        }

        // Build headers
        $headers = [
            'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
            'Access-Control-Allow-Headers' => $request->headers->get('Access-Control-Request-Headers', '*'),
            'Access-Control-Expose-Headers' => env('CORS_EXPOSE_HEADERS', 'Content-Length'),
            'Access-Control-Max-Age' => env('CORS_MAX_AGE', 0),
        ];

        if ($setOrigin !== null) {
            $headers['Access-Control-Allow-Origin'] = $setOrigin;
        }

        if ($allowCredentials) {
            $headers['Access-Control-Allow-Credentials'] = 'true';
        }

        // If preflight, return 204 with headers quickly
        if ($request->getMethod() === 'OPTIONS') {
            return response('', 204, $headers);
        }

        $response = $next($request);

        // Ensure headers are added to response (do not overwrite existing CORS headers)
        foreach ($headers as $key => $value) {
            if (! $response->headers->has($key)) {
                $response->headers->set($key, $value);
            }
        }

        return $response;
    }
}
