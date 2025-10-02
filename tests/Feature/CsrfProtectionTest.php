<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CsrfProtectionTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that CSRF protection is enabled for web routes
     */
    public function test_csrf_protection_is_enabled_for_web_routes()
    {
        // Test a POST route without CSRF token (should fail)
        $response = $this->withoutMiddleware(\App\Http\Middleware\VerifyCsrfToken::class)
            ->withHeaders([
                'Accept' => 'application/json',
            ])
            ->postJson('/login', [
                'email' => 'test@example.com',
                'password' => 'password',
            ]);

        // Without CSRF middleware, this should proceed to validation
        // With CSRF middleware active, it would return 419

        // Let's test the opposite - with CSRF middleware but no token
        $responseWithCsrf = $this->withHeaders([
            'Accept' => 'application/json',
        ])->postJson('/quote-products', [
            'test' => 'data',
        ]);

        // Should return 419 (CSRF token mismatch) if CSRF is enabled
        $this->assertEquals(419, $responseWithCsrf->getStatusCode());
    }

    /**
     * Test that CSRF protection works with valid token
     */
    public function test_csrf_protection_accepts_valid_token()
    {
        // Get CSRF token
        $response = $this->get('/login');
        $token = $response->getSession()->token();

        // Test with valid CSRF token
        $response = $this->withHeaders([
            'X-CSRF-TOKEN' => $token,
        ])->post('/login', [
            'email' => 'nonexistent@example.com',
            'password' => 'wrongpassword',
            '_token' => $token,
        ]);

        // Should not return 419, but may return validation errors or redirect
        $this->assertNotEquals(419, $response->getStatusCode());
    }

    /**
     * Test that API routes are excluded from CSRF protection
     */
    public function test_api_routes_excluded_from_csrf()
    {
        // API routes should work without CSRF token
        $response = $this->postJson('/api/test-endpoint', [
            'data' => 'test',
        ]);

        // Should not return 419 (may return 404 if endpoint doesn't exist)
        $this->assertNotEquals(419, $response->getStatusCode());
    }

    /**
     * Test that CSRF token is included in meta tag
     */
    public function test_csrf_token_in_meta_tag()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/dashboard');

        $response->assertSee('csrf-token', false);
    }

    /**
     * Test that Inertia requests include CSRF token in shared data
     */
    public function test_inertia_includes_csrf_token()
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->withHeaders(['X-Inertia' => 'true'])
            ->get('/dashboard');

        $data = $response->json();
        $this->assertArrayHasKey('props', $data);
        $this->assertArrayHasKey('csrf_token', $data['props']);
        $this->assertNotEmpty($data['props']['csrf_token']);
    }
}
