<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-API-KEY') ?? $request->input('api_key');

        if (!$apiKey) {
            return response()->json([
                'error' => 'API key is required'
            ], 401);
        }

        // Check if API key exists in database
        $validKey = \DB::table('api_keys')
            ->where('key', $apiKey)
            ->where('is_active', true)
            ->first();

        if (!$validKey) {
            return response()->json([
                'error' => 'Invalid API key'
            ], 401);
        }

        // Attach the API key info to the request
        $request->merge(['api_key_id' => $validKey->id]);

        return $next($request);
    }
}
