<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class VerifyServiceApiKey
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $apiKey = $request->header('X-Service-Api-Key');
        $expectedApiKey = config('app.service_api_key');

        // Skip validation if no API key is configured (development mode)
        if (empty($expectedApiKey)) {
            return $next($request);
        }

        if ($apiKey !== $expectedApiKey) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - Invalid API Key',
            ], 401);
        }

        return $next($request);
    }
}
