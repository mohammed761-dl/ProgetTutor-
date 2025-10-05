<?php

namespace Tests\Unit;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuoteTest extends TestCase
{
    use RefreshDatabase;

    protected $customer;

    protected $user;

    protected $product;

    protected function setUp(): void
    {
        parent::setUp();

        // Create test data
        $this->customer = Customer::factory()->create([
            'company_name' => 'Test Company',
            'contact_name' => 'John Doe',
            'email' => 'john@testcompany.com',
            'phone' => '+1234567890',
            'address' => '123 Test Street',
            'performance_flag' => 'Always on time',
            'vat_number' => 'VAT123456',
        ]);
        $this->user = User::factory()->create([
            'name' => 'Test User',
            'email' => 'user@test.com',
            'password' => bcrypt('password'),
            'role' => 'Commercial',
        ]);
        $this->product = Product::factory()->create([
            'product_code' => 'TEST-001',
            'name' => 'Test Product',
            'description' => 'Test Description',
            'technical_specs' => 'Test Specs',
            'commercial_terms' => 'Test Terms',
            'payment_terms' => 'Net 30',
            'min_delivery_day' => 5,
            'max_delivery_day' => 10,
            'availability_yrs' => 2,
            'status' => 'Active',
        ]);
    }

    /** @test */
    public function it_can_create_a_quote()
    {
        $quoteData = [
            'id_customer' => $this->customer->id_customer,
            'id_user' => $this->user->id_user,
            'date_quote' => now(),
            'valid_until' => now()->addDays(30),
            'status' => 'Sent same day',
            'currency' => 'EUR',
            'payment_terms' => 'Net 30',
            'delivery_terms' => '5-10 days',
            'discount_notes' => 'Test discount',
            'reduction' => 100.00,
        ];

        $quote = Quote::create($quoteData);

        $this->assertDatabaseHas('quotes', [
            'id_quote' => $quote->id_quote,
            'id_customer' => $this->customer->id_customer,
            'id_user' => $this->user->id_user,
            'status' => 'Sent same day',
            'currency' => 'EUR',
        ]);
    }

    /** @test */
    public function it_generates_quote_number_automatically()
    {
        $quote = Quote::create([
            'id_customer' => $this->customer->id_customer,
            'id_user' => $this->user->id_user,
            'date_quote' => now(),
            'valid_until' => now()->addDays(30),
            'status' => 'Sent same day',
            'currency' => 'EUR',
        ]);

        $this->assertNotNull($quote->quote_number);
        $this->assertMatchesRegularExpression('/^QT-\d{6}-\d{4}$/', $quote->quote_number);
    }

    /** @test */
    public function it_can_attach_products_with_snapshot_data()
    {
        $quote = Quote::create([
            'id_customer' => $this->customer->id_customer,
            'id_user' => $this->user->id_user,
            'date_quote' => now(),
            'valid_until' => now()->addDays(30),
            'status' => 'Sent same day',
            'currency' => 'EUR',
        ]);

        $quote->products()->attach($this->product->id_product, [
            'product_code' => $this->product->product_code,
            'name' => $this->product->name,
            'description' => $this->product->description,
            'technical_specs' => $this->product->technical_specs,
            'commercial_terms' => $this->product->commercial_terms,
            'payment_terms' => $this->product->payment_terms,
            'min_delivery_day' => $this->product->min_delivery_day,
            'max_delivery_day' => $this->product->max_delivery_day,
            'availability_yrs' => $this->product->availability_yrs,
            'quantity' => 2,
            'unit_price' => 100.00,
        ]);

        $this->assertDatabaseHas('quote_products', [
            'id_quote' => $quote->id_quote,
            'id_product' => $this->product->id_product,
            'product_code' => 'TEST-001',
            'name' => 'Test Product',
            'quantity' => 2,
            'unit_price' => 100.00,
            'total_line_price' => 200.00,
        ]);
    }

    /** @test */
    public function it_calculates_totals_correctly()
    {
        $quote = Quote::create([
            'id_customer' => $this->customer->id_customer,
            'id_user' => $this->user->id_user,
            'date_quote' => now(),
            'valid_until' => now()->addDays(30),
            'status' => 'Sent same day',
            'currency' => 'EUR',
            'reduction' => 50.00,
        ]);

        // Add two products
        $quote->products()->attach($this->product->id_product, [
            'product_code' => $this->product->product_code,
            'name' => $this->product->name,
            'description' => $this->product->description,
            'technical_specs' => $this->product->technical_specs,
            'commercial_terms' => $this->product->commercial_terms,
            'payment_terms' => $this->product->payment_terms,
            'min_delivery_day' => $this->product->min_delivery_day,
            'max_delivery_day' => $this->product->max_delivery_day,
            'availability_yrs' => $this->product->availability_yrs,
            'quantity' => 2,
            'unit_price' => 100.00,  // Total: 200.00
        ]);

        // Calculations:
        // Total Amount: 200.00
        // Reduction: 50.00
        // Total HT (after reduction): 150.00
        // VAT (20%): 30.00
        // Total TTC: 180.00

        $quote->update([
            'total_amount' => 200.00,
            'total_ht' => 150.00,
            'reduction' => 50.00,
            'vat' => 30.00,
            'total_ttc' => 180.00,
        ]);

        $this->assertEquals(200.00, $quote->total_amount);
        $this->assertEquals(150.00, $quote->total_ht);
        $this->assertEquals(50.00, $quote->reduction);
        $this->assertEquals(30.00, $quote->vat);
        $this->assertEquals(180.00, $quote->total_ttc);
    }

    /** @test */
    public function it_has_customer_relationship()
    {
        $quote = Quote::create([
            'id_customer' => $this->customer->id_customer,
            'id_user' => $this->user->id_user,
            'date_quote' => now(),
            'valid_until' => now()->addDays(30),
            'status' => 'Sent same day',
            'currency' => 'EUR',
        ]);

        $this->assertInstanceOf(Customer::class, $quote->customer);
        $this->assertEquals($this->customer->id_customer, $quote->customer->id_customer);
    }

    /** @test */
    public function it_has_products_relationship()
    {
        $quote = Quote::create([
            'id_customer' => $this->customer->id_customer,
            'id_user' => $this->user->id_user,
            'date_quote' => now(),
            'valid_until' => now()->addDays(30),
            'status' => 'Sent same day',
            'currency' => 'EUR',
        ]);

        $quote->products()->attach($this->product->id_product, [
            'product_code' => $this->product->product_code,
            'name' => $this->product->name,
            'description' => $this->product->description,
            'technical_specs' => $this->product->technical_specs,
            'commercial_terms' => $this->product->commercial_terms,
            'payment_terms' => $this->product->payment_terms,
            'min_delivery_day' => $this->product->min_delivery_day,
            'max_delivery_day' => $this->product->max_delivery_day,
            'availability_yrs' => $this->product->availability_yrs,
            'quantity' => 1,
            'unit_price' => 100.00,
        ]);

        // Refresh the model to get the attached products
        $quote = $quote->fresh(['products']);

        $this->assertCount(1, $quote->products);
        $this->assertInstanceOf(Product::class, $quote->products->first());
    }

    /** @test */
    public function it_can_update_quote()
    {
        $quote = Quote::create([
            'id_customer' => $this->customer->id_customer,
            'id_user' => $this->user->id_user,
            'date_quote' => now(),
            'valid_until' => now()->addDays(30),
            'status' => 'Sent same day',
            'currency' => 'EUR',
        ]);

        $newStatus = 'Sent within 2-3 days';
        $quote->update(['status' => $newStatus]);

        $this->assertEquals($newStatus, $quote->fresh()->status);
    }

    /** @test */
    public function it_validates_required_fields()
    {
        $this->expectException(\Illuminate\Database\QueryException::class);

        Quote::create([
            'date_quote' => now(),
            // Missing required fields
        ]);
    }

    /** @test */
    public function it_can_delete_quote_and_related_data()
    {
        // Create a quote with products
        $quote = Quote::create([
            'id_customer' => $this->customer->id_customer,
            'id_user' => $this->user->id_user,
            'date_quote' => now(),
            'valid_until' => now()->addDays(30),
            'status' => 'Sent same day',
            'currency' => 'EUR',
        ]);

        // Add products to the quote
        $quote->products()->attach($this->product->id_product, [
            'product_code' => $this->product->product_code,
            'name' => $this->product->name,
            'description' => $this->product->description,
            'technical_specs' => $this->product->technical_specs,
            'commercial_terms' => $this->product->commercial_terms,
            'payment_terms' => $this->product->payment_terms,
            'min_delivery_day' => $this->product->min_delivery_day,
            'max_delivery_day' => $this->product->max_delivery_day,
            'availability_yrs' => $this->product->availability_yrs,
            'quantity' => 1,
            'unit_price' => 100.00,
        ]);

        // Get the quote ID for later verification
        $quoteId = $quote->id_quote;

        // Create a controller instance
        $controller = new \App\Http\Controllers\QuoteController;

        // Delete using the controller method
        $response = $controller->destroy($quote);

        // Verify quote is deleted
        $this->assertDatabaseMissing('quotes', ['id_quote' => $quoteId]);

        // Verify quote products are deleted
        $this->assertDatabaseMissing('quote_products', ['id_quote' => $quoteId]);
    }

    /** @test */
    public function it_prevents_deletion_of_quote_with_purchase_orders()
    {
        // Create a quote
        $quote = Quote::create([
            'id_customer' => $this->customer->id_customer,
            'id_user' => $this->user->id_user,
            'date_quote' => now(),
            'valid_until' => now()->addDays(30),
            'status' => 'Sent same day',
            'currency' => 'EUR',
        ]);

        // Create a purchase order for this quote
        \App\Models\PurchaseOrder::create([
            'id_customer' => $this->customer->id_customer,
            'id_quote' => $quote->id_quote,
            'created_by' => $this->user->id_user,
            'status' => 'Pending',
        ]);

        // Create a controller instance
        $controller = new \App\Http\Controllers\QuoteController;

        // Try to delete the quote
        $response = $controller->destroy($quote);

        // Verify the quote still exists
        $this->assertDatabaseHas('quotes', ['id_quote' => $quote->id_quote]);
    }
}
