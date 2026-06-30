<?php

namespace App\Traits;

trait JwtTrait
{
    protected function generateJwtToken($user, array $extraClaims = []): string
    {
        $header = json_encode(['typ' => 'JWT', 'alg' => 'HS256']);

        $payload = array_merge([
            'iss'  => config('app.url'),
            'aud'  => config('app.url'),
            'sub'  => $user->id,
            'email' => $user->email,
            'name' => $user->name,
            'iat'  => time(),
            'exp'  => time() + (60 * 60),
        ], $extraClaims);

        return $this->encodeJwt($header, $payload);
    }

    protected function encodeJwt(string $header, array $payload): string
    {
        $headerB64 = $this->base64urlEncode($header);
        $payloadB64 = $this->base64urlEncode(json_encode($payload));
        
        $secret = config('app.key');
        if (str_starts_with($secret, 'base64:')) {
            $secret = base64_decode(substr($secret, 7));
        }
        
        $signature = hash_hmac('sha256', $headerB64 . '.' . $payloadB64, $secret, true);
        $signatureB64 = $this->base64urlEncode($signature);

        return $headerB64 . '.' . $payloadB64 . '.' . $signatureB64;
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