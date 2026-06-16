<?php

declare(strict_types=1);

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use PHPOpenSourceSaver\JWTAuth\Facades\JWTAuth;
use PHPOpenSourceSaver\JWTAuth\Exceptions\JWTException;
use Symfony\Component\HttpFoundation\Response;

class AttemptAuthentication
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if ($token !== null) {
            try {
                JWTAuth::setToken($token)->authenticate();
            } catch (JWTException) {
                // Token inválido/expirado: segue como anônimo, sem abortar.
            }
        }

        return $next($request);
    }
}