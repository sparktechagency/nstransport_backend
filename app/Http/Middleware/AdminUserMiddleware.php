<?php

namespace App\Http\Middleware;

use Closure;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class AdminUserMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        try {
            $user = Auth::user();

            if ($user->role == 'User'||$user->role == 'Admin') {
                return $next($request);
            }

            return response()->json([
                'message' => 'You are not an user.',
            ], 401);

        } catch (Exception $exception) {
            return response()->json([
                'message' => 'Unauthorized: ' . $exception->getMessage(),
            ], 401);
        }
    }
}
