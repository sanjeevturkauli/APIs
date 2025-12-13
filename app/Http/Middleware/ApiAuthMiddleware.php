<?php

namespace App\Http\Middleware;

use App\Traits\ResponseHandler;
use Closure;
use Illuminate\Http\Request;
use Laravel\Sanctum\PersonalAccessToken;

class ApiAuthMiddleware
{
    use ResponseHandler;

    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return $this->error(401, 'Unauthorized', [], false);
        }

        $accessToken = PersonalAccessToken::findToken($token);

        if (!$accessToken) {
            return $this->error(401, 'You are unauthorized', [], false);
        }

        $request->setUserResolver(function () use ($accessToken) {
            return $accessToken->tokenable;
        });

        return $next($request);
    }
}
