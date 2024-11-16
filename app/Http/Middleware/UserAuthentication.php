<?php

namespace App\Http\Middleware;

use App\Http\Controllers\Auth\JWTAuth;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class UserAuthentication
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if(empty($request->header('Authorization')) || !$request->header('Authorization') || $request->header('Authorization') == ''){
            return response()->json([
                'status' => false,
                'message' => 'Please provide your authorization token in the request header',
            ],401);
        }else{
            $token = JWTAuth::verifyToken($request->header('Authorization'),false);
        }

        if($token && $token->role === 'user'){
            $request->headers->set('email',$token->email);
            $request->headers->set('id',$token->id);
            return $next($request);
        }else{
            return response()->json([
                'status' => false,
                'message' => 'Unauthorized',
            ],401);
        }
    }
}
