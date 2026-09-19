<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

/**
 * Authenticates the CALLER (e.g. "institute-portal", "back-office"), not
 * an end institute. Each calling system gets one API key in config/sms.php
 * (backed by .env). No database involved — this service has none.
 */
class AuthenticateSmsClient
{
    public function handle(Request $request, Closure $next)
    {
        $apiKey = $request->header('X-API-Key');
        $clients = config('sms.clients', []);

        if (!$apiKey || !isset($clients[$apiKey])) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Unauthorized: missing or invalid X-API-Key header.',
            ], 401);
        }

        // Available to the controller/logs as request()->attributes->get('client_name')
        $request->attributes->set('client_name', $clients[$apiKey]);

        return $next($request);
    }
}
