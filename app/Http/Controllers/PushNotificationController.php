<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use App\Models\User;
use App\Models\Post;
use Illuminate\Routing\Controller as BaseController;

class PushNotificationController extends BaseController
{
    private const HMS_API_URL = 'https://push-api.cloud.huawei.com';

    private static $accessToken = null;
    private static $tokenExpiry = null;

    /**
     * Register user's push token
     */
    public function registerPushToken(Request $request)
    {
        $request->validate([
            'push_token' => 'required|string',
            'platform' => 'required|string|in:huawei,fcm',
        ]);

        $user = auth()->user();
        $user->push_token = $request->push_token;
        $user->push_platform = $request->platform;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Push token registered successfully',
        ]);
    }

    /**
     * Unregister user's push token
     */
    public function unregisterPushToken()
    {
        $user = auth()->user();
        $user->push_token = null;
        $user->push_platform = null;
        $user->save();

        return response()->json([
            'status' => true,
            'message' => 'Push token unregistered successfully',
        ]);
    }

    /**
     * Send comment notification to post owner
     */
    public function sendCommentNotification(Request $request)
    {
        $request->validate([
            'post_id' => 'required|integer',
            'comment_id' => 'required|integer',
            'commenter_name' => 'required|string',
            'comment_content' => 'required|string',
        ]);

        // Get post and owner
        $post = Post::findOrFail($request->post_id);
        $postOwner = User::findOrFail($post->owner_id);

        // Don't send notification if commenter is the post owner
        if (auth()->id() === $postOwner->id) {
            return response()->json([
                'status' => true,
                'message' => 'No notification sent (same user)',
            ]);
        }

        // Check if post owner has push token
        if (!$postOwner->push_token || $postOwner->push_platform !== 'huawei') {
            return response()->json([
                'status' => false,
                'message' => 'Post owner does not have HMS push token',
            ]);
        }

        // Send push notification
        $title = "New Comment on Your Post";
        $body = "{$request->commenter_name} commented: " . substr($request->comment_content, 0, 50);

        $result = $this->sendHmsPushNotification(
            $postOwner->push_token,
            $title,
            $body,
            [
                'type' => 'comment',
                'post_id' => (string)$request->post_id,
                'comment_id' => (string)$request->comment_id,
            ]
        );

        if ($result) {
            return response()->json([
                'status' => true,
                'message' => 'Notification sent successfully',
            ]);
        }

        return response()->json([
            'status' => false,
            'message' => 'Failed to send notification',
        ]);
    }

    /**
     * Get HMS OAuth 2.0 access token
     */
    private function getAccessToken()
    {
        // Return cached token if still valid
        if (self::$accessToken && self::$tokenExpiry && now()->lt(self::$tokenExpiry)) {
            return self::$accessToken;
        }

        $clientId = config('services.hms.client_id');
        $clientSecret = config('services.hms.client_secret');

        if (!$clientId || !$clientSecret) {
            Log::error('HMS credentials not configured');
            return null;
        }

        $response = Http::asForm()->post('https://oauth-login.cloud.huawei.com/oauth2/v3/token', [
            'grant_type' => 'client_credentials',
            'client_id' => $clientId,
            'client_secret' => $clientSecret,
        ]);

        if ($response->successful()) {
            $data = $response->json();
            self::$accessToken = $data['access_token'];
            self::$tokenExpiry = now()->addSeconds($data['expires_in'] - 60);

            return self::$accessToken;
        }

        Log::error('Failed to get HMS access token', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return null;
    }

    /**
     * Send push notification via HMS API
     */
    private function sendHmsPushNotification($pushToken, $title, $body, $data = [])
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            Log::error('Cannot send HMS push: No access token');
            return false;
        }

        $clientId = config('services.hms.client_id');

        $payload = [
            'validate_only' => false,
            'message' => [
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'android' => [
                    'notification' => [
                        'click_action' => [
                            'type' => 3, // Open app
                        ],
                    ],
                ],
                'data' => json_encode($data),
                'token' => [$pushToken],
            ],
        ];

        $response = Http::withHeaders([
            'Authorization' => "Bearer {$accessToken}",
            'Content-Type' => 'application/json',
        ])->post(self::HMS_API_URL . '/v1/' . $clientId . '/messages:send', $payload);

        if ($response->successful()) {
            Log::info('HMS push notification sent successfully', [
                'token' => $pushToken,
                'title' => $title,
            ]);
            return true;
        }

        Log::error('Failed to send HMS push notification', [
            'status' => $response->status(),
            'body' => $response->body(),
        ]);

        return false;
    }
}
