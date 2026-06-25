<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class VerifyUserServiceToken
{
    public function handle(Request $request, Closure $next)
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json(['message' => 'Unauthorized. Bearer Token is missing.'], 401);
        }

        try {
            $response = Http::withToken($token)
                ->acceptJson()
                ->timeout(15)
                ->get('http://user_service:8000/api/user');

            if ($response->successful()) {
                $userData = $response->json();
                
                // Inject user_id and role into the request attributes so controllers can use it
                $request->attributes->add([
                    'user_id' => $userData['id'],
                    'role' => $userData['role'] ?? 'user'
                ]);
                
                return $next($request);
            }
        } catch (\Exception $e) {
            // Suppress connection exceptions and fallback to 401
        }

        return response()->json(['message' => 'Unauthorized. Invalid or expired token.'], 401);
    }
}
