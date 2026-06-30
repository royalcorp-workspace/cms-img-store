<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class JwtMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        $authHeader = $request->header('Authorization');
        
        if (!$authHeader || !str_starts_with($authHeader, 'Bearer ')) {
            Log::warning('JWT: No token provided', ['ip' => $request->ip(), 'path' => $request->path()]);
            return response()->json([
                'success' => false,
                'status_code' => 401,
                'message' => 'Token not provided',
            ], 401);
        }

        $token = substr($authHeader, 7);
        $parts = explode('.', $token);

        if (count($parts) !== 3) {
            Log::warning('JWT: Invalid token format', ['ip' => $request->ip()]);
            return response()->json([
                'success' => false,
                'status_code' => 401,
                'message' => 'Invalid token format',
            ], 401);
        }

        try {
            $payload = json_decode($this->base64urlDecode($parts[1]), true);
            
            if ($payload['exp'] < time()) {
                Log::warning('JWT: Token expired', ['user_id' => $payload['sub'] ?? null]);
            return response()->json([
                'success' => false,
                'status_code' => 401,
                'message' => 'Token expired',
            ], 401);
            }

            $secret = config('app.key');
            if (str_starts_with($secret, 'base64:')) {
                $secret = base64_decode(substr($secret, 7));
            }
            
            $signature = hash_hmac('sha256', $parts[0] . '.' . $parts[1], $secret, true);
            $expectedSignature = $this->base64urlEncode($signature);

            if (!hash_equals($expectedSignature, $parts[2])) {
                Log::warning('JWT: Invalid signature', ['user_id' => $payload['sub'] ?? null]);
            return response()->json([
                'success' => false,
                'status_code' => 401,
                'message' => 'Invalid token signature',
            ], 401);
            }

            Log::info('JWT: Token validated', ['user_id' => $payload['sub']]);
            $request->attributes->set('auth_user_id', $payload['sub']);
            
        } catch (\Exception $e) {
            Log::error('JWT: Token error', ['error' => $e->getMessage()]);
            return response()->json([
                'success' => false,
                'status_code' => 401,
                'message' => 'Invalid token',
            ], 401);
        }

        return $next($request);
    }

    protected function base64urlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    protected function base64urlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}