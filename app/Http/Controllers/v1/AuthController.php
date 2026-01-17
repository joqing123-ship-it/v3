<?php

namespace App\Http\Controllers\v1;

use App\Http\Controllers\v1\Controller;
use App\Http\Resources\v1\userResource;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Mail\OtpEmail;
use Illuminate\Support\Facades\Mail;
use App\Services\HuaweiAuthService;
use Illuminate\Support\Facades\Log;


class AuthController extends Controller
{
    public function register(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|unique:users,email',
            'password' => 'required|min:6',
            'role' => 'nullable|string',
        ]);

        $user = User::create([
            'email' => strtolower($credentials['email']),
            'password' => Hash::make($credentials['password']),
            'role' => $credentials['role'] ?? 'worker',
            'email_verified_at' => now(),
        ]);

        $token = $user->createToken('mobile')->plainTextToken;

        return response()->json([
            'message' => 'Registration successful',
            'status' => true,
            'data' => [
                'token' => $token,
                'user' => new userResource($user->load('profile'))
            ]
        ], 201);
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
            'password' => 'required',
            'role' => 'required|string',
        ]);
        $user = User::where('email', strtolower($credentials['email']))->first(); // get the user based one email



        if (!$user || !Hash::check($credentials["password"], $user->password)|| $user->role !== $credentials['role']) {
            return response()->json([
                'message' => 'invalid credentials',
                'status' => false
            ], 200);
        }

        //  if(!$user->hasVerifiedEmail()){
        // $otp = rand(1000, 9999); // 4-digit OTP

        // $user->otp = $otp;

        // $user->save();
        // Mail::to($user->email)->send( new OtpEmail(otp: $otp));
        //     return response()->json([
        //         'message' => 'unverified',
        //         'status' => false
        //     ], 200);

        // }
        // $user->save();
        // Mail::to($user->email)->send( new OtpEmail(otp: $otp));
        //     return response()->json([
        //         'message' => 'unverified',
        //         'status' => false
        //     ], 200);

        // }
        $token  = $user->createToken('mobile')->plainTextToken; // get the token


        return response()->json(['message' => 'success', 'status' => true, 'data' => ['token' => $token, 'user' => new userResource($user->load('profile'))]]); // pass to frontend
    }
    public function logout(Request $request)
    {

        $request->user()->tokens()->delete();
        return response()->json([
            'message' => "Logged out successfully",
            'status' => true,

        ], 200);
    }



    public function forgetPassword(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email',
        ]);
        $user = User::where('email', $credentials["email"])->first();

        if (!$user) {
            return response()->json(['message' => 'invalid email', 'status' => false], 200); // get the user based one email
        }

        // $otp = rand(1000, 9999); // 4-digit OTP

        // $user->otp = $otp;

        // $user->save();


        // Mail::to($user->email)->send(new OtpEmail(otp: $otp));
        return response()->json(['message' => 'OTP sent successfully', 'status' => true], 200); // get the user based one email
    }


    public function submitOtp(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|exists:users,email',
            'otp' => 'required|numeric|digits:4',
        ]);
        $user = User::where('email', operator: $credentials["email"])->first();
        if ($user->otp == $credentials["otp"]) {
            $user->markEmailAsVerified();
            $user->otp = null;
            $user->save();
            return response()->json(['status' => true, 'message' => 'OTP submitted successfully'], 200); // get the user based one email
        } else {
            return response()->json(['status' => false, 'message' => 'invalid otp']); // get the user based one email
        }
    } //sendOtp


    public function changePassword(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|exists:users,email',
            'password' => 'required',
        ]);
        $user = User::where('email', $credentials["email"])->first();
        $user->password = Hash::make($credentials["password"]);
        $user->save();
        return response()->json(['status' => true, "message" => "password changed successfully"], 200); // get the user based one email
    }

    /**
     * Authenticate user with Huawei Account Kit
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function huaweiLogin(Request $request)
    {
        // Validate incoming request
        $credentials = $request->validate([
            'idToken' => 'required|string',
            'email' => 'nullable|email',
            'displayName' => 'nullable|string',
            'openId' => 'required|string',
            'unionId' => 'nullable|string',
            'avatarUri' => 'nullable|string',
        ]);

        try {
            // Initialize Huawei Auth Service
            $huaweiService = new HuaweiAuthService();

            // Verify the ID token (use server-side verification if configured)
            if (config('services.huawei.verify_token')) {
                $tokenPayload = $huaweiService->verifyIdTokenWithHuawei($credentials['idToken']);
            } else {
                $tokenPayload = $huaweiService->verifyIdToken($credentials['idToken']);
            }

            if (!$tokenPayload) {
                Log::warning('Huawei ID token verification failed', [
                    'openId' => $credentials['openId']
                ]);

                return response()->json([
                    'message' => 'Invalid Huawei ID token',
                    'status' => false
                ], 401);
            }

            // Extract user info from token
            $userInfo = $huaweiService->extractUserInfo($tokenPayload);

            // Check if the openId from request matches the token
            if ($userInfo['open_id'] !== $credentials['openId']) {
                Log::warning('Huawei OpenID mismatch', [
                    'request_openId' => $credentials['openId'],
                    'token_openId' => $userInfo['open_id']
                ]);

                return response()->json([
                    'message' => 'OpenID mismatch',
                    'status' => false
                ], 401);
            }

            // Try to find existing user by Huawei OpenID
            $user = User::where('huawei_open_id', $credentials['openId'])->first();

            if (!$user && !empty($credentials['email'])) {
                // Check if user exists with this email but different auth provider
                $user = User::where('email', strtolower($credentials['email']))->first();

                if ($user) {
                    // Link Huawei account to existing email account
                    $user->update([
                        'huawei_open_id' => $credentials['openId'],
                        'huawei_union_id' => $credentials['unionId'] ?? null,
                        'display_name' => $credentials['displayName'] ?? $user->display_name,
                        'avatar_uri' => $credentials['avatarUri'] ?? $user->avatar_uri,
                        'auth_provider' => 'huawei',
                        'email_verified_at' => $user->email_verified_at ?? now(),
                    ]);

                    // Create profile if doesn't exist
                    if (!$user->profile) {
                        $user->profile()->create([
                            'name' => $credentials['displayName'] ?? $user->display_name ?? $user->email,
                            'description' => 'Linked with Huawei ID',
                        ]);
                    }

                    Log::info('Linked Huawei account to existing user', [
                        'user_id' => $user->id,
                        'email' => $user->email
                    ]);
                }
            }

            if (!$user) {
                // Create new user with Huawei account
                $user = User::create([
                    'email' => !empty($credentials['email']) ? strtolower($credentials['email']) : null,
                    'huawei_open_id' => $credentials['openId'],
                    'huawei_union_id' => $credentials['unionId'] ?? null,
                    'display_name' => $credentials['displayName'] ?? 'Huawei User',
                    'avatar_uri' => $credentials['avatarUri'] ?? null,
                    'password' => Hash::make(uniqid()), // Random password for Huawei users
                    'role' => 'worker',
                    'auth_provider' => 'huawei',
                    'email_verified_at' => now(), // Huawei users are pre-verified
                ]);

                // Create profile for the new user
                $user->profile()->create([
                    'name' => $credentials['displayName'] ?? 'Huawei User',
                    'description' => 'Registered via Huawei ID',
                ]);

                // Create worker record if role is worker
                if ($user->role === 'worker') {
                    $user->worker()->create([
                        'department' => 'general',
                    ]);
                }

                Log::info('Created new user with Huawei account', [
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'openId' => $credentials['openId']
                ]);
            } else {
                // Update existing user's information
                $user->update([
                    'display_name' => $credentials['displayName'] ?? $user->display_name,
                    'avatar_uri' => $credentials['avatarUri'] ?? $user->avatar_uri,
                    'huawei_union_id' => $credentials['unionId'] ?? $user->huawei_union_id,
                ]);

                Log::info('Updated existing Huawei user', [
                    'user_id' => $user->id
                ]);
            }

            // Generate authentication token
            $token = $user->createToken('mobile')->plainTextToken;

            return response()->json([
                'message' => 'Login successful',
                'status' => true,
                'data' => [
                    'token' => $token,
                    'user' => new userResource($user->load('profile'))
                ]
            ], 200);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'message' => 'Validation failed',
                'status' => false,
                'errors' => $e->errors()
            ], 422);
        } catch (\Exception $e) {
            Log::error('Huawei login failed', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            return response()->json([
                'message' => 'Login failed. Please try again.',
                'status' => false
            ], 500);
        }
    }
}
