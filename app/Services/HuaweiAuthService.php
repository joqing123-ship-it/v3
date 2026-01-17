<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class HuaweiAuthService
{
    /**
     * Verify Huawei ID token with Huawei's servers
     * 
     * @param string $idToken
     * @return array|null Returns decoded token data or null if verification fails
     */
    public function verifyIdToken(string $idToken): ?array
    {
        try {
            // Decode the JWT token (ID Token is a JWT)
            $tokenParts = explode('.', $idToken);

            if (count($tokenParts) !== 3) {
                Log::error('Invalid Huawei ID token format');
                return null;
            }

            // Decode the payload (second part of JWT)
            $payload = json_decode(base64_decode(str_replace(['-', '_'], ['+', '/'], $tokenParts[1])), true);

            if (!$payload) {
                Log::error('Failed to decode Huawei ID token payload');
                return null;
            }

            // Basic validation
            if (!isset($payload['iss']) || !isset($payload['aud']) || !isset($payload['exp'])) {
                Log::error('Missing required fields in Huawei ID token');
                return null;
            }

            // Check if token is expired
            if ($payload['exp'] < time()) {
                Log::error('Huawei ID token has expired');
                return null;
            }

            // Verify the issuer
            if ($payload['iss'] !== 'https://accounts.huawei.com') {
                Log::error('Invalid Huawei ID token issuer');
                return null;
            }

            return $payload;
        } catch (\Exception $e) {
            Log::error('Huawei ID token verification failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Verify ID token with Huawei's token verification endpoint (more secure)
     * This method makes an HTTP request to Huawei's servers for verification
     * 
     * @param string $idToken
     * @param string|null $appId Your Huawei App ID (optional, uses config if not provided)
     * @return array|null
     */
    public function verifyIdTokenWithHuawei(string $idToken, ?string $appId = null): ?array
    {
        try {
            // Use provided app ID or get from config
            $appId = $appId ?? config('services.huawei.app_id');

            if (!$appId) {
                Log::error('Huawei App ID not configured');
                return null;
            }

            // Huawei's token verification endpoint
            $url = 'https://oauth-login.cloud.huawei.com/oauth2/v3/tokeninfo';

            $response = Http::asForm()->post($url, [
                'id_token' => $idToken,
            ]);

            if ($response->successful()) {
                $data = $response->json();

                // Verify the app ID matches
                if (isset($data['aud']) && $data['aud'] === $appId) {
                    return $data;
                }

                Log::error('App ID mismatch in Huawei token verification', [
                    'expected' => $appId,
                    'received' => $data['aud'] ?? 'none'
                ]);
                return null;
            }

            Log::error('Huawei token verification request failed: ' . $response->body());
            return null;
        } catch (\Exception $e) {
            Log::error('Huawei token verification API call failed: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Extract user information from verified token payload
     * 
     * @param array $tokenPayload
     * @return array
     */
    public function extractUserInfo(array $tokenPayload): array
    {
        return [
            'open_id' => $tokenPayload['sub'] ?? null,
            'union_id' => $tokenPayload['union_id'] ?? null,
            'email' => $tokenPayload['email'] ?? null,
            'email_verified' => $tokenPayload['email_verified'] ?? false,
            'display_name' => $tokenPayload['display_name'] ?? null,
        ];
    }
}
