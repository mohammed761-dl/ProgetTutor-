<?php

namespace Tests\Unit;

use App\Models\Admin;
use App\Models\CompanyInfo;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected $admin;
    protected $companyInfo;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test admin
        $this->admin = Admin::factory()->create([
            'name' => 'Test Admin',
            'email' => 'admin@test.com',
            'password' => Hash::make('password123'),
            'status' => 'Active'
        ]);

        // Create company info
        $this->companyInfo = CompanyInfo::create([
            'name' => 'Test Company',
            'address' => '123 Test Street',
            'phone' => '+1234567890',
            'email' => 'info@testcompany.com',
            'website' => 'https://testcompany.com',
            'warranty_duration' => 12,
            'support_info' => 'Test support info',
            'bank_name' => 'Test Bank',
            'swift_bic' => 'TESTBIC123',
            'account_number' => '123456789',
            'iban' => 'TEST123456789',
            'authorized_name' => 'John Doe',
            'authorized_title' => 'CEO',
            'signature_email' => 'ceo@testcompany.com',
            'signature_phone' => '+1234567891',
            'general_conditions_url' => 'https://testcompany.com/terms',
            'vat_number' => 'VAT123456'
        ]);
    }

    /** @test */
    public function admin_can_access_profile()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->get('/Profile');

        $response->assertOk();
        $response->assertSee($this->admin->name);
        $response->assertSee($this->admin->email);
    }

    /** @test */
    public function admin_can_update_company_info()
    {
        $this->actingAs($this->admin, 'admin');

        $updatedData = [
            'name' => 'Updated Company',
            'address' => '456 New Street',
            'phone' => '+9876543210',
            'email' => 'new@company.com',
            'website' => 'https://newcompany.com',
            'warranty_duration' => 24,
            'support_info' => 'New support info',
            'bank_name' => 'New Bank',
            'swift_bic' => 'NEWBIC123',
            'account_number' => '987654321',
            'iban' => 'NEW123456789',
            'authorized_name' => 'Jane Doe',
            'authorized_title' => 'CTO',
            'signature_email' => 'cto@newcompany.com',
            'signature_phone' => '+9876543211',
            'general_conditions_url' => 'https://newcompany.com/terms',
            'vat_number' => 'VAT987654'
        ];

        $response = $this->put('/serp-admin/company-info', $updatedData);

        $response->assertRedirect('/Profile');
        $this->assertDatabaseHas('company_info', [
            'name' => 'Updated Company',
            'email' => 'new@company.com',
            'vat_number' => 'VAT987654'
        ]);
    }

    /** @test */
    public function admin_can_update_profile()
    {
        $this->actingAs($this->admin, 'admin');

        $updatedData = [
            'name' => 'Updated Admin',
            'email' => 'updated@admin.com'
        ];

        $response = $this->put('/serp-admin/profile', $updatedData);

        $response->assertRedirect('/Profile');
        $this->assertDatabaseHas('admins', [
            'name' => 'Updated Admin',
            'email' => 'updated@admin.com'
        ]);
    }

    /** @test */
    public function admin_can_change_password()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->put('/serp-admin/change-password', [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertRedirect('/Profile');
        
        // Verify password was changed
        $this->admin->refresh();
        $this->assertTrue(Hash::check('newpassword123', $this->admin->password));
    }

    /** @test */
    public function admin_cannot_change_password_with_wrong_current_password()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->put('/serp-admin/change-password', [
            'current_password' => 'wrongpassword',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123'
        ]);

        $response->assertSessionHasErrors('current_password');
        
        // Verify password was not changed
        $this->admin->refresh();
        $this->assertTrue(Hash::check('password123', $this->admin->password));
    }

    /** @test */
    public function admin_cannot_update_profile_with_existing_email()
    {
        // Create another admin
        $otherAdmin = Admin::factory()->create([
            'name' => 'Other Admin',
            'email' => 'other@admin.com',
            'password' => Hash::make('password123'),
            'status' => 'Active'
        ]);

        $this->actingAs($this->admin, 'admin');

        $response = $this->put('/serp-admin/profile', [
            'name' => 'Updated Admin',
            'email' => 'other@admin.com' // Try to use existing email
        ]);

        $response->assertInvalid(['email']);
        
        // Verify email was not changed
        $this->admin->refresh();
        $this->assertEquals('admin@test.com', $this->admin->email);
    }

    /** @test */
    public function unauthenticated_admin_cannot_access_profile()
    {
        $response = $this->get('/Profile');

        $response->assertRedirect('/serp-admin-login');
    }

    /** @test */
    public function company_info_validation_works()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->put('/serp-admin/company-info', [
            // Missing required fields
            'name' => '',
            'email' => 'invalid-email',
            'warranty_duration' => 'invalid'
        ]);

        $response->assertInvalid(['name', 'email', 'warranty_duration']);
    }

    /** @test */
    public function profile_update_validation_works()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->put('/serp-admin/profile', [
            'name' => '', // Empty name
            'email' => 'invalid-email' // Invalid email
        ]);

        $response->assertInvalid(['name', 'email']);
    }

    /** @test */
    public function password_change_validation_works()
    {
        $this->actingAs($this->admin, 'admin');

        $response = $this->put('/serp-admin/change-password', [
            'current_password' => 'password123',
            'password' => 'short', // Too short
            'password_confirmation' => 'different' // Doesn't match
        ]);

        $response->assertInvalid(['password']);
    }
}
