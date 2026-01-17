<?php

namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class HuaweiAuthTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test Huawei login endpoint exists
     */
    public function test_huawei_login_endpoint_exists(): void
    {
        $response = $this->postJson('/api/v1/huawei-login', []);

        // Should not return 404, should return validation error instead
        $this->assertNotEquals(404, $response->status());
    }

    /**
     * Test Huawei login validation
     */
    public function test_huawei_login_requires_fields(): void
    {
        $response = $this->postJson('/api/v1/huawei-login', []);

        $response->assertStatus(422);
        $response->assertJsonValidationErrors(['idToken', 'openId']);
    }

    /**
     * Test Huawei login with invalid token
     */
    public function test_huawei_login_rejects_invalid_token(): void
    {
        $response = $this->postJson('/api/v1/huawei-login', [
            'idToken' => 'invalid_token',
            'openId' => 'test_open_id',
            'email' => 'test@example.com',
            'displayName' => 'Test User'
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => false,
            'message' => 'Invalid Huawei ID token'
        ]);
    }

    /**
     * Test that user is created on first Huawei login
     * Note: This test uses a mock valid JWT token structure
     */
    public function test_creates_new_user_on_first_huawei_login(): void
    {
        // Create a basic JWT-like token (for testing structure, not real validation)
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss' => 'https://accounts.huawei.com',
            'sub' => 'test_open_id_123',
            'aud' => 'test_app_id',
            'exp' => time() + 3600,
            'email' => 'newuser@example.com',
            'display_name' => 'New User'
        ]));
        $signature = base64_encode('fake_signature');
        $mockToken = "$header.$payload.$signature";

        $response = $this->postJson('/api/v1/huawei-login', [
            'idToken' => $mockToken,
            'openId' => 'test_open_id_123',
            'email' => 'newuser@example.com',
            'displayName' => 'New User',
            'unionId' => 'test_union_id',
            'avatarUri' => 'https://example.com/avatar.jpg'
        ]);

        // Check user was created
        $this->assertDatabaseHas('users', [
            'email' => 'newuser@example.com',
            'huawei_open_id' => 'test_open_id_123',
            'display_name' => 'New User',
            'auth_provider' => 'huawei'
        ]);

        $response->assertStatus(200);
        $response->assertJsonStructure([
            'message',
            'status',
            'data' => [
                'token',
                'user'
            ]
        ]);
    }

    /**
     * Test that existing user can be linked with Huawei account
     */
    public function test_links_huawei_account_to_existing_email_user(): void
    {
        // Create existing user with email/password
        $existingUser = User::factory()->create([
            'email' => 'existing@example.com',
            'password' => bcrypt('password123'),
            'auth_provider' => 'email'
        ]);

        // Create mock Huawei token
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss' => 'https://accounts.huawei.com',
            'sub' => 'huawei_open_id_456',
            'aud' => 'test_app_id',
            'exp' => time() + 3600,
            'email' => 'existing@example.com'
        ]));
        $signature = base64_encode('fake_signature');
        $mockToken = "$header.$payload.$signature";

        $response = $this->postJson('/api/v1/huawei-login', [
            'idToken' => $mockToken,
            'openId' => 'huawei_open_id_456',
            'email' => 'existing@example.com',
            'displayName' => 'Existing User'
        ]);

        // Refresh user from database
        $existingUser->refresh();

        // Check Huawei account was linked
        $this->assertEquals('huawei_open_id_456', $existingUser->huawei_open_id);
        $this->assertEquals('huawei', $existingUser->auth_provider);

        // Should not create duplicate user
        $this->assertEquals(1, User::where('email', 'existing@example.com')->count());

        $response->assertStatus(200);
    }

    /**
     * Test Huawei login with existing Huawei user
     */
    public function test_returns_existing_user_on_repeated_login(): void
    {
        // Create user with Huawei account
        $user = User::factory()->create([
            'email' => 'huawei@example.com',
            'huawei_open_id' => 'existing_huawei_id',
            'auth_provider' => 'huawei'
        ]);

        // Create mock token
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss' => 'https://accounts.huawei.com',
            'sub' => 'existing_huawei_id',
            'aud' => 'test_app_id',
            'exp' => time() + 3600,
            'email' => 'huawei@example.com'
        ]));
        $signature = base64_encode('fake_signature');
        $mockToken = "$header.$payload.$signature";

        $response = $this->postJson('/api/v1/huawei-login', [
            'idToken' => $mockToken,
            'openId' => 'existing_huawei_id',
            'email' => 'huawei@example.com',
            'displayName' => 'Huawei User'
        ]);

        // Should still be only one user
        $this->assertEquals(1, User::where('huawei_open_id', 'existing_huawei_id')->count());

        $response->assertStatus(200);
        $response->assertJson([
            'status' => true,
            'message' => 'Login successful'
        ]);
    }

    /**
     * Test OpenID mismatch detection
     */
    public function test_rejects_openid_mismatch(): void
    {
        // Create mock token with different OpenID
        $header = base64_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
        $payload = base64_encode(json_encode([
            'iss' => 'https://accounts.huawei.com',
            'sub' => 'token_open_id',
            'aud' => 'test_app_id',
            'exp' => time() + 3600,
            'email' => 'test@example.com'
        ]));
        $signature = base64_encode('fake_signature');
        $mockToken = "$header.$payload.$signature";

        $response = $this->postJson('/api/v1/huawei-login', [
            'idToken' => $mockToken,
            'openId' => 'different_open_id', // Mismatch!
            'email' => 'test@example.com'
        ]);

        $response->assertStatus(401);
        $response->assertJson([
            'status' => false,
            'message' => 'OpenID mismatch'
        ]);
    }
}
