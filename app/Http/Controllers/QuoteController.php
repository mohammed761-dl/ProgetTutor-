<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Product;
use App\Models\Quote;
use App\Models\QuoteCustomer;
use App\Models\QuoteProduct;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

/**
 * QuoteController - Handles all quote-related operations
 *
 * ARCHITECTURE PRINCIPLES:
 * 1. BACKEND-FIRST CALCULATIONS: All financial calculations happen in the backend
 * 2. FRONTEND DISPLAY ONLY: Vue.js and Blade templates only display data, no calculations
 * 3. SNAPSHOT DATA INTEGRITY: Print/PDF use historical data from quote_customers/quote_products tables
 * 4. VAT RATE HANDLING: User's VAT rate is stored as decimal (0.10 for 10%) and used consistently
 *
 * VAT RATE FLOW:
 * - Frontend sends: 10 (percentage)
 * - Frontend converts to: 0.10 (decimal) before sending to backend
 * - Backend stores: 0.10 in database
 * - Backend calculates: VAT amount using stored rate
 * - Display shows: "VAT (10.0%): EUR 10.00"
 */
class QuoteController extends Controller
{
    /**
     * Display all quotes with search and filtering capabilities
     *
     * PURPOSE: Frontend only displays data - no calculations performed here
     * DATA SOURCE: Live database data (not snapshots)
     *
     * @param  Request  $request  - Search, status, and PO status filters
     * @return Inertia\Inertia - Renders Quotes/Index.vue with filtered quotes
     */
    public function index(Request $request)
    {
        $query = Quote::with([
            'customer',
            'customerSnapshot',
            'products',
            'quoteProducts',
            'user',
        ]);

        // Search functionality across multiple fields
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('quote_number', 'like', "%{$search}%")
                    ->orWhere('currency', 'like', "%{$search}%")
                    ->orWhere('total_amount', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('company_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('user', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Filter by quote status
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Filter by PO status (has purchase order or not)
        if ($request->has('po_status') && $request->po_status) {
            $hasPO = $request->po_status === 'has_po';
            $query->where('has_po', $hasPO);
        }

        $quotes = $query->orderBy('created_at', 'desc')->get();

        return Inertia::render('Quotes/Index', [
            'quotes' => $quotes,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'po_status' => $request->po_status,
            ],
        ]);
    }

    /**
     * Show quote creation form
     *
     * PURPOSE: Provides data for dropdowns - no calculations
     * DATA: Customers, users, and active products for form selection
     *
     * @return Inertia\Inertia - Renders Quotes/Create.vue with form data
     */
    public function create()
    {
        $customers = Customer::orderBy('company_name')->get();
        $users = User::orderBy('name')->get();
        $products = Product::where('status', 'Active')->orderBy('name')->get();

        return Inertia::render('Quotes/Create', [
            'customers' => $customers,
            'users' => $users,
            'products' => $products,
            'currentUser' => Auth::user() ?? null,
        ]);
    }

    /**
     * Create new quote with all financial calculations performed in backend
     *
     * ARCHITECTURE: Frontend sends raw data, backend calculates and stores all totals
     * VAT RATE: Accepts decimal values (0.10 for 10%), defaults to 0.20 if not provided
     * SNAPSHOTS: Creates customer and product snapshots for historical integrity
     *
     * @param  Request  $request  - Validated quote data including products array
     * @return Redirect - To quotes index with success message
     *
     * FINANCIAL CALCULATIONS:
     * 1. Subtotal = Sum of (quantity × unit_price) for all products
     * 2. Total after reduction = Subtotal - reduction amount
     * 3. VAT amount = Total after reduction × VAT rate
     * 4. Final total = Total after reduction + VAT amount
     */
    public function store(Request $request)
    {
        try {
            $quoteNumber = Quote::generateQuoteNumber();

            // Debug: Log what's being received
            \Log::info('Quote creation request data:', $request->all());
            \Log::info('VAT rate received:', ['vat_rate' => $request->input('vat_rate')]);

            // Validate all input data
            $validated = $request->validate([
                'id_customer' => 'exists:customers,id_customer',
                'id_user' => 'exists:users,id_user',
                'date_quote' => 'date',
                'valid_until' => 'date|after:date_quote',
                'status' => [Rule::in(['Sent same day', 'Sent within 2-3 days', 'Sent after 4+ days'])],
                'currency' => [Rule::in(['EUR', 'USD', 'MAD'])],
                'payment_terms' => 'nullable|string',
                'delivery_terms' => 'nullable|string',
                'discount_notes' => 'nullable|string',
                'reduction' => 'nullable|numeric|min:0',
                'vat_rate' => 'nullable|numeric|min:0|max:1', // Accepts decimals (0.10 for 10%)
                'signature_name' => 'nullable|string',
                'signature_title' => 'nullable|string',
                'products' => 'array',
                'products.*.id_product' => 'exists:products,id_product',
                'products.*.quantity' => 'integer|min:1',
                'products.*.unit_price' => 'numeric|min:0',
            ]);

            $quote = DB::transaction(function () use ($validated, $quoteNumber) {
                // Create quote with initial values (totals will be calculated and updated)
                $quote = Quote::create([
                    'quote_number' => $quoteNumber,
                    'id_customer' => $validated['id_customer'],
                    'id_user' => $validated['id_user'],
                    'date_quote' => $validated['date_quote'],
                    'valid_until' => $validated['valid_until'],
                    'status' => $validated['status'],
                    'currency' => $validated['currency'],
                    'payment_terms' => $validated['payment_terms'],
                    'delivery_terms' => $validated['delivery_terms'],
                    'discount_notes' => $validated['discount_notes'],
                    'reduction' => $validated['reduction'] ?? 0,
                    'vat_rate' => $validated['vat_rate'] ?? 0.20, // Save user's VAT rate
                    'total_ht' => 0, // Will be calculated
                    'vat' => 0, // Will be calculated
                    'total_ttc' => 0, // Will be calculated
                    'signature_name' => $validated['signature_name'],
                    'signature_title' => $validated['signature_title'],
                ]);

                // Create customer snapshot for historical data integrity
                $customer = Customer::findOrFail($validated['id_customer']);
                QuoteCustomer::createWithSnapshot($quote->id_quote, $customer);

                // Create product snapshots with all product details
                foreach ($validated['products'] as $productData) {
                    $product = Product::findOrFail($productData['id_product']);
                    QuoteProduct::create([
                        'id_quote' => $quote->id_quote,
                        'id_product' => $product->id_product,
                        'product_code' => $product->product_code,
                        'name' => $product->name,
                        'description' => $product->description,
                        'technical_specs' => $product->technical_specs,
                        'commercial_terms' => $product->commercial_terms,
                        'payment_terms' => $product->payment_terms,
                        'min_delivery_day' => $product->min_delivery_day,
                        'max_delivery_day' => $product->max_delivery_day,
                        'availability_yrs' => $product->availability_yrs,
                        'quantity' => $productData['quantity'],
                        'unit_price' => $productData['unit_price'],
                        'total_line_price' => $productData['quantity'] * $productData['unit_price'],
                    ]);
                }

                // BACKEND CALCULATION: Calculate all financial totals
                $totalAmount = 0;
                foreach ($validated['products'] as $productData) {
                    $lineTotal = $productData['quantity'] * $productData['unit_price'];
                    $totalAmount += $lineTotal;
                }

                // Apply reduction and calculate VAT
                $reduction = $validated['reduction'] ?? 0;
                $vatRate = $validated['vat_rate'] ?? 0.20; // Default to 20% if not provided
                $totalAfterReduction = $totalAmount - $reduction;
                $vat = $totalAfterReduction * $vatRate;
                $totalTtc = $totalAfterReduction + $vat;

                // Update quote with calculated totals
                $quote->update([
                    'total_amount' => $totalAmount,
                    'reduction' => $reduction,
                    'vat_rate' => $vatRate,
                    'vat' => $vat,
                    'total_ttc' => $totalTtc,
                ]);

                return $quote;
            });

            return redirect()->route('quotes.index')
                ->with('success', 'Quote created successfully!');

        } catch (\Exception $e) {
            return back()->withErrors(['error' => $e->getMessage()])->withInput();
        }
    }

    /**
     * Display the specified quote
     *
     * PURPOSE: Show detailed view of a single quote
     * DATA SOURCE: Live database data with relationships
     *
     * @param  Quote  $quote  - Quote model instance
     * @return Inertia\Inertia - Renders Quotes/Show.vue
     */
    public function show(Quote $quote)
    {
        $quote->load([
            'customer',
            'user',
            'products',
            'customerSnapshot',
        ]);

        return Inertia::render('Quotes/Show', [
            'quote' => $quote,
        ]);
    }

    /**
     * Show the form for editing the specified quote
     *
     * PURPOSE: Load existing quote data for editing
     * DATA: Current quote data, customers, users, and products for form
     *
     * @param  Quote  $quote  - Quote model instance
     * @return Inertia\Inertia - Renders Quotes/Edit.vue with quote data
     */
    public function edit(Quote $quote)
    {
        $customers = Customer::orderBy('company_name')->get();
        $users = User::orderBy('name')->get();
        $products = Product::where('status', 'Active')->orderBy('name')->get();

        $quote->load(['customer', 'user', 'products']);

        return Inertia::render('Quotes/Edit', [
            'quote' => $quote,
            'customers' => $customers,
            'users' => $users,
            'products' => $products,
            'currentUser' => Auth::user() ?? null,
        ]);
    }

    /**
     * Update the specified quote in storage
     *
     * ARCHITECTURE: Similar to store method - backend calculates all totals
     * VAT RATE: Uses user's input or defaults to 20%
     * SNAPSHOTS: Updates product snapshots with new data
     *
     * @param  Request  $request  - Validated update data
     * @param  Quote  $quote  - Quote model instance
     * @return Redirect - To quotes index with success message
     */
    public function update(Request $request, Quote $quote)
    {
        try {
            // Validate all input data
            $validated = $request->validate([
                'id_customer' => 'required|exists:customers,id_customer',
                'id_user' => 'required|exists:users,id_user',
                'date_quote' => 'required|date',
                'valid_until' => 'required|date|after:date_quote',
                'status' => ['required', Rule::in(['Sent same day', 'Sent within 2-3 days', 'Sent after 4+ days'])],
                'currency' => ['required', Rule::in(['EUR', 'USD', 'MAD'])],
                'payment_terms' => 'nullable|string',
                'delivery_terms' => 'nullable|string',
                'discount_notes' => 'nullable|string',
                'reduction' => 'nullable|numeric|min:0',
                'vat_rate' => 'nullable|numeric|min:0|max:1', // Accepts decimals (0.10 for 10%)
                'signature_name' => 'nullable|string',
                'signature_title' => 'nullable|string',
                'products' => 'required|array|min:1',
                'products.*.id_product' => 'required|exists:products,id_product',
                'products.*.quantity' => 'required|integer|min:1',
                'products.*.unit_price' => 'required|numeric|min:0',
            ]);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        }

        try {
            DB::transaction(function () use ($validated, $quote) {
                $totalHt = 0;
                $reduction = $validated['reduction'] ?? 0;
                $vatRate = $validated['vat_rate'] ?? 0.20; // Default to 20% if not provided

                // Update basic quote data (excluding products)
                $quoteData = collect($validated)->except(['products'])->toArray();
                $quote->update($quoteData);

                // Update products with new snapshot data
                $quote->products()->detach(); // Remove old relationships

                foreach ($validated['products'] as $productData) {
                    $product = Product::findOrFail($productData['id_product']);

                    // Calculate line total
                    $lineTotal = $productData['quantity'] * $productData['unit_price'];
                    $totalHt += $lineTotal;

                    // Attach product with new snapshot data
                    $quote->products()->attach($product->id_product, [
                        'product_code' => $product->product_code,
                        'name' => $product->name,
                        'description' => $product->description,
                        'technical_specs' => $product->technical_specs,
                        'commercial_terms' => $product->commercial_terms,
                        'payment_terms' => $product->payment_terms,
                        'min_delivery_day' => $product->min_delivery_day,
                        'max_delivery_day' => $product->max_delivery_day,
                        'availability_yrs' => $product->availability_yrs,
                        'quantity' => $productData['quantity'],
                        'unit_price' => $productData['unit_price'],
                    ]);
                }

                // Calculate new totals
                $totalAfterReduction = $totalHt - $reduction;
                $vat = $totalAfterReduction * $vatRate;
                $totalTtc = $totalAfterReduction + $vat;

                // Update quote with new calculated totals
                $quote->update([
                    'total_amount' => $totalHt,
                    'total_ht' => $totalAfterReduction,
                    'reduction' => $reduction,
                    'vat_rate' => $vatRate,
                    'vat' => $vat,
                    'total_ttc' => $totalTtc,
                ]);
            });

            return redirect()->route('quotes.index')
                ->with('success', 'Quote updated successfully!');

        } catch (\Illuminate\Database\QueryException $e) {
            return back()->withErrors([
                'error' => 'Unable to update the quote due to a data conflict. Please check your product quantities and prices, then try again.',
            ])->withInput();

        } catch (\Exception $e) {
            return back()->withErrors([
                'error' => 'An unexpected error occurred while updating the quote. Please try again or contact support if the problem persists.',
            ])->withInput();
        }
    }

    /**
     * Remove the specified quote from storage
     *
     * SAFETY CHECKS: Prevents deletion if quote has associated purchase orders
     * CLEANUP: Removes all related data (products, snapshots, documents)
     *
     * @param  Quote  $quote  - Quote model instance
     * @return Redirect - To quotes index with success/error message
     */
    public function destroy(Quote $quote)
    {
        try {
            DB::transaction(function () use ($quote) {
                // Safety check: prevent deletion if quote has purchase orders
                if ($quote->purchaseOrders()->exists()) {
                    throw new \Exception('Cannot delete quote: it has associated purchase orders.');
                }

                // Clean up all related data
                $quote->products()->detach();

                if ($quote->customerSnapshot) {
                    $quote->customerSnapshot->delete();
                }

                $quote->documents()->delete();

                // Finally delete the quote
                $quote->delete();
            });

            return redirect()->route('quotes.index')
                ->with('success', 'Quote deleted successfully!');
        } catch (\Exception $e) {
            return back()->withErrors(['error' => 'Failed to delete quote: '.$e->getMessage()]);
        }
    }

    /**
     * Calculate financial totals for preview (AJAX endpoint)
     *
     * PURPOSE: Real-time calculation updates in frontend forms
     * ARCHITECTURE: Frontend sends data, backend calculates, returns results
     * VAT RATE: Accepts decimal values (0.10 for 10%), defaults to 0.20 if not provided
     *
     * @param  Request  $request  - Products array, reduction, and VAT rate
     * @return JsonResponse - Calculated totals (total_amount, reduction, vat, total_ttc)
     *
     * USAGE: Called from Create.vue and Edit.vue when products/reduction/VAT rate change
     */
    public function calculateTotals(Request $request)
    {
        $validated = $request->validate([
            'products' => 'required|array|min:1',
            'products.*.quantity' => 'required|integer|min:1',
            'products.*.unit_price' => 'required|numeric|min:0',
            'reduction' => 'nullable|numeric|min:0',
            'vat_rate' => 'nullable|numeric|min:0|max:1', // Accepts decimals (0.10 for 10%)
        ]);

        $vatRate = $validated['vat_rate'] ?? 0.20; // Default to 20% if not provided
        $reduction = $validated['reduction'] ?? 0;

        // Calculate total amount (before reduction)
        $totalAmount = 0;
        foreach ($validated['products'] as $product) {
            $quantity = intval($product['quantity']);
            $unitPrice = floatval($product['unit_price']);
            $totalAmount += $quantity * $unitPrice;
        }

        // Calculate totals with proper number handling
        $totalAfterReduction = $totalAmount - floatval($reduction);
        $vat = $totalAfterReduction * $vatRate;
        $totalTtc = $totalAfterReduction + $vat;

        return response()->json([
            'total_amount' => $totalAmount,  // Total before reduction (subtotal)
            'reduction' => $reduction,
            'vat' => $vat,
            'total_ttc' => $totalTtc,  // Final total
        ]);
    }

    /**
     * Get next quote number for preview
     *
     * PURPOSE: Generate unique quote number for frontend display
     * USAGE: Called from Create.vue to show next available quote number
     *
     * @return JsonResponse - Next available quote number
     */
    public function getNextQuoteNumber()
    {
        return response()->json([
            'quote_number' => Quote::generateQuoteNumber(),
        ]);
    }

    /**
     * Print view for Quote
     *
     * ARCHITECTURE: Uses snapshot data for historical integrity
     * PURPOSE: Display quote for printing (same data as PDF download)
     * DATA SOURCE: quote_customers and quote_products tables (snapshots)
     *
     * @param  Quote  $quote  - Quote model instance
     * @return Inertia\Inertia - Renders Quotes/Print.vue with snapshot data
     *
     * SNAPSHOT LOGIC:
     * - Always uses customerSnapshot (historical customer data)
     * - Always uses quoteProducts (historical product data)
     * - Ensures old quotes show original data even if customer/product details change
     */
    public function printView(Quote $quote)
    {
        $quote->load(['user']);

        // ALWAYS use customer snapshot for print - ensures historical data integrity
        $customerSnapshot = $quote->customerSnapshot;
        if (! $customerSnapshot) {
            // If no snapshot exists, create one from current customer data
            $customer = $quote->customer;
            if ($customer) {
                $customerSnapshot = QuoteCustomer::createWithSnapshot($quote->id_quote, $customer);
            }
        }

        // ALWAYS use quote products (snapshot data) for print
        $quoteProducts = $quote->quoteProducts;
        if ($quoteProducts->isEmpty()) {
            return back()->withErrors(['error' => 'Quote has no products to display.']);
        }

        // Format products for the template
        $products = $quoteProducts->map(function ($qp) {
            return [
                'id' => $qp->id_product,
                'product_code' => $qp->product_code ?? '',
                'name' => $qp->name ?? '',
                'description' => $qp->description ?? '',
                'quantity' => $qp->quantity ?? 0,
                'unit_price' => $qp->unit_price ?? 0,
                'total_line_price' => $qp->total_line_price ?? 0,
            ];
        });

        return Inertia::render('Quotes/Print', [
            'quote' => [
                'id_quote' => $quote->id_quote,
                'quote_number' => $quote->quote_number,
                'date_quote' => $quote->date_quote,
                'valid_until' => $quote->valid_until,
                'status' => $quote->status,
                'currency' => $quote->currency,
                'payment_terms' => $quote->payment_terms,
                'delivery_terms' => $quote->delivery_terms,
                'discount_notes' => $quote->discount_notes,
                'reduction' => $quote->reduction,
                'vat_rate' => $quote->vat_rate, // Include stored VAT rate
                'vat' => $quote->vat,
                'total_amount' => $quote->total_amount, // Subtotal before reduction
                'total_ttc' => $quote->total_ttc, // Final total
                'signature_name' => $quote->signature_name,
                'signature_title' => $quote->signature_title,
                'customer' => $customerSnapshot, // ALWAYS use snapshot
                'user' => $quote->user,
            ],
            'QuoteProduct' => $products, // ALWAYS use snapshot products
        ]);
    }

    /**
     * Download PDF for Quote
     *
     * ARCHITECTURE: Uses snapshot data for historical integrity (same as printView)
     * PURPOSE: Generate PDF file for download
     * DATA SOURCE: quote_customers and quote_products tables (snapshots)
     * TEMPLATE: resources/views/pdf/quote.blade.php
     *
     * @param  Quote  $quote  - Quote model instance
     * @return Response - PDF file download
     *
     * IMPORTANT: This method uses the EXACT same data structure as printView
     * to ensure consistency between print view and PDF download.
     */
    public function downloadPdf(Quote $quote)
    {
        try {
            $quote->load(['user']);

            // Use customer snapshot for historical accuracy (same as printView)
            $customerSnapshot = $quote->customerSnapshot;
            if (! $customerSnapshot) {
                $customer = $quote->customer;
                if ($customer) {
                    $customerSnapshot = QuoteCustomer::createWithSnapshot($quote->id_quote, $customer);
                }
            }

            // Use quote products (snapshot data) for historical accuracy (same as printView)
            $quoteProducts = $quote->quoteProducts;
            if ($quoteProducts->isEmpty()) {
                return back()->withErrors(['error' => 'Quote has no products to display.']);
            }

            // Format products for the PDF template (same structure as printView)
            $products = $quoteProducts->map(function ($qp) {
                return [
                    'id' => $qp->id_product,
                    'product_code' => $qp->product_code ?? '',
                    'name' => $qp->name ?? '',
                    'description' => $qp->description ?? '',
                    'quantity' => $qp->quantity ?? 0,
                    'unit_price' => $qp->unit_price ?? 0,
                    'total_line_price' => $qp->total_line_price ?? 0,
                ];
            });

            // Prepare data for PDF template (EXACT same structure as printView)
            $pdfData = [
                'quote' => [
                    'id_quote' => $quote->id_quote,
                    'quote_number' => $quote->quote_number,
                    'date_quote' => $quote->date_quote,
                    'valid_until' => $quote->valid_until,
                    'status' => $quote->status,
                    'currency' => $quote->currency,
                    'payment_terms' => $quote->payment_terms,
                    'delivery_terms' => $quote->delivery_terms,
                    'discount_notes' => $quote->discount_notes,
                    'reduction' => $quote->reduction,
                    'vat_rate' => $quote->vat_rate,
                    'vat' => $quote->vat,
                    'total_amount' => $quote->total_amount,
                    'total_ttc' => $quote->total_ttc,
                    'signature_name' => $quote->signature_name,
                    'signature_title' => $quote->signature_title,
                    'customer' => $customerSnapshot,
                    'user' => $quote->user,
                ],
                'products' => $products,
            ];

            // Generate PDF using the same data structure as printView
            $pdf = Pdf::loadView('pdf.quote', $pdfData);

            return $pdf->download($quote->quote_number.'.pdf');

        } catch (\Exception $e) {
            return back()->with('error', 'Error generating PDF. Please try again.');
        }
    }
}
