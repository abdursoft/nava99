<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\JWTAuth;
use App\Models\User;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class QTAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(!empty($request->header('Wallet-Session')) || env('QT_PASS_KEY') == $request->header('Pass-Key')){
            if(!empty($request->header('Wallet-Session'))){
                $token = JWTAuth::verifyToken($request->header('Wallet-Session'),false);
                $request->headers->set('id', $token->id);
            }
            return $next($request);
        }else{
            return response()->json([
                "code" => "INVALID_PASS_KEY",
                "message" => "Authentication error"
            ],401);
        }

    }
}
