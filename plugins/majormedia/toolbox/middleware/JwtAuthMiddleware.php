<?php

namespace MajorMedia\ToolBox\Middleware;

use Closure;
use ErrorCodes;
use Illuminate\Http\Request;
use Majormedia\ToolBox\Traits\RetrieveUser;

class JwtAuthMiddleware
{
    use RetrieveUser;

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $this->retrieveUser(false);

        if (!$user) {
            return response()->json([
                'status' => 'error',
                'code' => ErrorCodes::AUTHENTICATION_ERROR,
            ], 401);
        }
        $request->merge(['auth_user' => $user]);

        return $next($request);
    }
}
