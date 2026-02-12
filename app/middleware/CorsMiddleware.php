<?php

namespace App\Middleware;

use Closure;
use Illuminate\Http\Request;

class CorsMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    // public function handle(Request $request, Closure $next)
    // {
    //     // Handle OPTIONS request (preflight request)
    //     if ($request->getMethod() === 'OPTIONS') {
    //         return response()->json('OK', 200)
    //             ->header('Access-Control-Allow-Origin', '*') // or specify your domain here
    //             ->header('Access-Control-Allow-Methods', 'POST, GET, DELETE, PUT, OPTIONS')
    //             ->header('Access-Control-Allow-Headers', 'x-requested-with, Content-Type, origin, authorization, accept, client-security-token')
    //             ->header('Access-Control-Max-Age', '1000');
    //     }

    //     // Add CORS headers for all other requests
    //     $response = $next($request);

    //     return $response
    //         ->header('Access-Control-Allow-Origin', '*') // or specify your domain here
    //         ->header('Access-Control-Allow-Methods', 'POST, GET, DELETE, PUT, OPTIONS')
    //         ->header('Access-Control-Allow-Headers', 'x-requested-with, Content-Type, origin, authorization, accept, client-security-token')
    //         ->header('Access-Control-Max-Age', '1000');
    // }

    public function handle(Request $request, Closure $next)
    {
        // Handle OPTIONS request (preflight request)
        if ($request->getMethod() === 'OPTIONS') {

            return response()->json('OK', 200)
                ->withHeaders([
                    'Access-Control-Allow-Origin' => '*',
                    'Access-Control-Allow-Methods' => 'POST, GET, DELETE, PUT, OPTIONS',
                    'Access-Control-Allow-Headers' => 'x-requested-with, Content-Type, origin, authorization, accept, client-security-token',
                    'Access-Control-Max-Age' => '1000',
                ]);
        }

        $response = $next($request);

        $headers = [
            'Access-Control-Allow-Origin' => '*',
            'Access-Control-Allow-Methods' => 'POST, GET, DELETE, PUT, OPTIONS',
            'Access-Control-Allow-Headers' => 'x-requested-with, Content-Type, origin, authorization, accept, client-security-token',
            'Access-Control-Max-Age' => '1000',
        ];

        foreach ($headers as $key => $value) {
            $response->headers->set($key, $value);
        }

        return $response;
    }

}
