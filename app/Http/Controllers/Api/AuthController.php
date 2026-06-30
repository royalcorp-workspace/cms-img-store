<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Traits\JwtTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AuthController extends ApiController
{
    use JwtTrait;

    /**
     * Login via Firebase ID Token (Google / any Firebase provider).
     *
     * Flow:
     *  1. FE kirim { firebase_token: "<Firebase ID Token>" }
     *  2. Backend decode header JWT Firebase → ambil `kid`
     *  3. Fetch Google public certificates (di-cache 1 jam)
     *  4. Verify signature RSA-SHA256 menggunakan cert yang sesuai
     *  5. Validasi claim: aud = FIREBASE_PROJECT_ID, iss, exp, iat
     *  6. Deteksi auth provider dari claim `firebase.sign_in_provider`
     *  7. Upsert user di database
     *  8. Generate JWT lokal dengan `auth_provider` di payload
     */
    public function login(Request $request)
    {
        Log::info('Firebase login attempt', ['ip' => $request->ip()]);

        $validator = Validator::make($request->all(), [
            'firebase_token' => 'required|string',
        ], [
            'firebase_token.required' => 'Field firebase_token wajib diisi.',
            'firebase_token.string'   => 'Field firebase_token harus berupa string.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validasi gagal. Pastikan request body berisi firebase_token.',
                422,
                $validator->errors()->toArray()
            );
        }

        $firebaseToken = $request->input('firebase_token');

        $verifiedClaims = $this->verifyFirebaseIdToken($firebaseToken);

        if ($verifiedClaims === null) {
            return $this->errorResponse('Invalid or expired Firebase token', 401);
        }

        $email        = $verifiedClaims['email'] ?? null;
        $firebaseUid  = $verifiedClaims['sub']   ?? null;
        $displayName  = $verifiedClaims['name']  ?? null;
        $photoUrl     = $verifiedClaims['picture'] ?? null;

        // Deteksi provider dari claim firebase.sign_in_provider
        $signInProvider = $verifiedClaims['firebase']['sign_in_provider'] ?? 'unknown';
        $isGoogleLogin  = ($signInProvider === 'google.com');
        $authProvider   = $isGoogleLogin ? 'google' : $signInProvider;

        if (!$email || !$firebaseUid) {
            Log::warning('Firebase token missing email or uid', ['claims' => $verifiedClaims]);
            return $this->errorResponse('Firebase token does not contain required user info', 422);
        }

        Log::info('Firebase token verified', [
            'email'         => $email,
            'uid'           => $firebaseUid,
            'auth_provider' => $authProvider,
        ]);

        // --- Upsert user ke database ---
        // Cari berdasarkan email atau firebase_uid (whichever matches first)
        $user = User::where('email', $email)
                    ->orWhere('firebase_uid', $firebaseUid)
                    ->first();

        if ($user) {
            // Update data Firebase yang mungkin berubah (nama, foto, uid)
            $user->update([
                'name'              => $displayName ?? $user->name,
                'firebase_uid'      => $firebaseUid,
                'auth_provider'     => $authProvider,
                'photo_url'         => $photoUrl ?? $user->photo_url,
                'email_verified'    => true,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);

            Log::info('User updated from Firebase', ['user_id' => $user->id, 'email' => $email]);
        } else {
            // Buat user baru
            $user = User::create([
                'id'                => $firebaseUid,
                'name'              => $displayName ?? $email,
                'email'             => $email,
                'password_hash'     => Hash::make(Str::random(32)),
                'email_verified'    => true,
                'email_verified_at' => now(),
                'firebase_uid'      => $firebaseUid,
                'auth_provider'     => $authProvider,
                'photo_url'         => $photoUrl,
            ]);

            Log::info('New user created from Firebase', ['user_id' => $user->id, 'email' => $email]);
        }

        // --- Generate JWT lokal dengan auth_provider ---
        $jwtToken = $this->generateJwtToken($user, [
            'auth_provider'    => $authProvider,
            'firebase_uid'     => $firebaseUid,
            'is_google_login'  => $isGoogleLogin,
        ]);

        Log::info('JWT token generated', [
            'user_id'       => $user->id,
            'auth_provider' => $authProvider,
        ]);

        return $this->successResponse([
            'user' => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'photo_url'     => $photoUrl,
                'auth_provider' => $authProvider,
                'is_google_login' => $isGoogleLogin,
            ],
            'token'         => $jwtToken,
            'token_type'    => 'Bearer',
        ], 'Login successful');
    }

    /**
     * Verifikasi Firebase ID Token secara manual (tanpa package eksternal).
     *
     * Referensi: https://firebase.google.com/docs/auth/admin/verify-id-tokens#verify_id_tokens_using_a_third-party_jwt_library
     *
     * @return array|null  Decoded payload claims jika valid, null jika tidak valid.
     */
    protected function verifyFirebaseIdToken(string $idToken): ?array
    {
        $parts = explode('.', $idToken);

        if (count($parts) !== 3) {
            Log::warning('Firebase token: bukan format JWT yang valid');
            return null;
        }

        [$headerB64, $payloadB64, $signatureB64] = $parts;

        // Decode header untuk mendapatkan `kid` (key ID)
        $header = json_decode($this->base64urlDecodeFirebase($headerB64), true);
        if (!$header || ($header['alg'] ?? '') !== 'RS256') {
            Log::warning('Firebase token: alg bukan RS256', ['alg' => $header['alg'] ?? null]);
            return null;
        }

        $kid = $header['kid'] ?? null;
        if (!$kid) {
            Log::warning('Firebase token: header tidak memiliki kid');
            return null;
        }

        // Decode payload
        $payload = json_decode($this->base64urlDecodeFirebase($payloadB64), true);
        if (!$payload) {
            Log::warning('Firebase token: payload tidak dapat di-decode');
            return null;
        }

        $projectId = config('services.firebase.project_id');

        // Validasi klaim standar
        $now = time();

        if (($payload['exp'] ?? 0) <= $now) {
            Log::warning('Firebase token: sudah expired', ['exp' => $payload['exp'] ?? null]);
            return null;
        }

        if (($payload['iat'] ?? 0) > $now) {
            Log::warning('Firebase token: iat di masa depan', ['iat' => $payload['iat'] ?? null]);
            return null;
        }

        if (($payload['aud'] ?? '') !== $projectId) {
            Log::warning('Firebase token: aud tidak cocok', [
                'expected' => $projectId,
                'actual'   => $payload['aud'] ?? null,
            ]);
            return null;
        }

        $expectedIss = "https://securetoken.google.com/{$projectId}";
        if (($payload['iss'] ?? '') !== $expectedIss) {
            Log::warning('Firebase token: iss tidak cocok', [
                'expected' => $expectedIss,
                'actual'   => $payload['iss'] ?? null,
            ]);
            return null;
        }

        if (empty($payload['sub'])) {
            Log::warning('Firebase token: sub kosong');
            return null;
        }

        // Fetch & cache Google public certificates
        $certs = $this->getGooglePublicCerts();
        if (!$certs || !isset($certs[$kid])) {
            Log::warning('Firebase token: public cert tidak ditemukan untuk kid', ['kid' => $kid]);
            return null;
        }

        // Verifikasi signature menggunakan openssl
        $certPem     = $certs[$kid];
        $pubKey      = openssl_get_publickey($certPem);
        $signedData  = $headerB64 . '.' . $payloadB64;
        $signature   = $this->base64urlDecodeFirebase($signatureB64);

        $verified = openssl_verify($signedData, $signature, $pubKey, OPENSSL_ALGO_SHA256);

        if ($verified !== 1) {
            Log::warning('Firebase token: signature tidak valid', ['kid' => $kid]);
            return null;
        }

        Log::debug('Firebase token: signature valid', ['uid' => $payload['sub']]);

        return $payload;
    }

    /**
     * Fetch Google public certificates untuk Firebase ID Token verification.
     * Di-cache selama 1 jam (atau sesuai Cache-Control header dari Google).
     */
    protected function getGooglePublicCerts(): ?array
    {
        return Cache::remember('firebase_google_public_certs', 3600, function () {
            $certsUrl = 'https://www.googleapis.com/robot/v1/metadata/x509/securetoken@system.gserviceaccount.com';

            try {
                $response = Http::timeout(10)->get($certsUrl);

                if (!$response->successful()) {
                    Log::error('Gagal fetch Google public certs', ['status' => $response->status()]);
                    return null;
                }

                return $response->json();
            } catch (\Exception $e) {
                Log::error('Exception saat fetch Google public certs', ['error' => $e->getMessage()]);
                return null;
            }
        });
    }

    /**
     * Base64url decode (digunakan untuk Firebase JWT, berbeda dengan JwtTrait).
     */
    protected function base64urlDecodeFirebase(string $data): string
    {
        $padded = str_pad(strtr($data, '-_', '+/'), strlen($data) + (4 - strlen($data) % 4) % 4, '=');
        return base64_decode($padded);
    }

    // -------------------------------------------------------------------------
    // Mock Login (development only)
    // -------------------------------------------------------------------------

    public function getMockLogin(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Method not allowed. Use POST with JSON body: {"email":"user@example.com","firebase_token":"<token>"}',
        ], 405);
    }

    public function mockLogin(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email'          => 'required|email',
            'firebase_token' => 'required|string',
        ], [
            'email.required'          => 'Field email wajib diisi.',
            'email.email'             => 'Format email tidak valid.',
            'firebase_token.required' => 'Field firebase_token wajib diisi.',
            'firebase_token.string'   => 'Field firebase_token harus berupa string.',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                'Validasi gagal.',
                422,
                $validator->errors()->toArray()
            );
        }

        $email = $request->input('email');

        $user = User::where('email', $email)->first();

        if (!$user) {
            $user = User::create([
                'id'                => (string) Str::uuid(),
                'name'              => $request->input('name', $email),
                'email'             => $email,
                'password_hash'     => Hash::make(Str::random(32)),
                'email_verified'    => true,
                'email_verified_at' => now(),
            ]);
        }

        $jwtToken = $this->generateJwtToken($user, [
            'auth_provider'   => 'mock',
            'is_google_login' => false,
        ]);

        Log::info('Mock JWT token generated', ['user_id' => $user->id, 'email' => $email]);

        return $this->successResponse([
            'user' => [
                'id'              => $user->id,
                'name'            => $user->name,
                'email'           => $user->email,
                'auth_provider'   => 'mock',
                'is_google_login' => false,
            ],
            'token'      => $jwtToken,
            'token_type' => 'Bearer',
        ], 'Mock login successful');
    }
}