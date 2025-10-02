<?php

namespace App\Http\Controllers;

use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\Quote;
use App\Models\User;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = Invoice::with([
            'deliveryNote.aro.purchaseOrder.customer',
            'quote.customer',
            'creator',
        ]);

        // Add search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhere('customer_name', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhere('currency', 'like', "%{$search}%")
                    ->orWhere('grand_total', 'like', "%{$search}%")
                    ->orWhereHas('quote', function ($quoteQuery) use ($search) {
                        $quoteQuery->where('quote_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('deliveryNote', function ($dnQuery) use ($search) {
                        $dnQuery->where('dnp_number', 'like', "%{$search}%");
                    });
            });
        }

        // Add status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Add overdue filter
        if ($request->has('overdue') && $request->overdue === 'true') {
            $query->where('due_date', '<', now()->format('Y-m-d'))
                ->where('status', '!=', 'Paid');
        }

        $invoices = $query->orderBy('issue_date', 'desc')
            ->get()
            ->map(function ($invoice) {
                return [
                    'id_invoice' => $invoice->id_invoice,
                    'invoice_number' => $invoice->invoice_number,
                    'delivery_note_number' => $invoice->deliveryNote ? $invoice->deliveryNote->dnp_number : null,
                    'quote_number' => $invoice->quote ? $invoice->quote->quote_number : null,
                    'customer_name' => $invoice->customer_name,
                    'status' => $invoice->status,
                    'issue_date' => $invoice->issue_date ? $invoice->issue_date->format('Y-m-d') : null,
                    'due_date' => $invoice->due_date ? $invoice->due_date->format('Y-m-d') : null,
                    'grand_total' => $invoice->grand_total,
                    'currency' => $invoice->currency,
                    'is_overdue' => $invoice->isOverdue(),
                    'source' => $invoice->quote ? 'quote' : 'delivery_note', // Indicate the source
                ];
            });

        return Inertia::render('Invoices/Index', [
            'invoices' => $invoices,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'overdue' => $request->overdue,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // Get quotes that have been sent (any status means it's been sent)
        $quotes = Quote::with(['customer'])
            ->whereIn('status', ['Sent same day', 'Sent within 2-3 days', 'Sent after 4+ days'])
            ->orderBy('date_quote', 'desc')
            ->get()
            ->map(function ($quote) {
                return [
                    'id_quote' => $quote->id_quote,
                    'quote_number' => $quote->quote_number,
                    'customer_name' => $quote->customer->company_name ?? 'N/A',
                    'customer_email' => $quote->customer->email ?? '',
                    'customer_phone' => $quote->customer->phone ?? '',
                    'customer_vat' => $quote->customer->vat_number ?? '',
                    'customer_address' => $quote->customer->address ?? '',
                    'customer_contact_name' => $quote->customer->contact_name ?? '',
                    'quote_date' => $quote->date_quote,
                    'total_amount' => $quote->total_ttc ?? $quote->total_amount,
                    'currency' => $quote->currency ?? 'EUR',
                    'payment_terms' => $quote->payment_terms ?? 'Net 30',
                ];
            });

        // Get users for the created_by field - just pass current user
        $currentUser = User::find(auth()->id());

        return Inertia::render('Invoices/Create', [
            'quotes' => $quotes,
            'currentUser' => $currentUser,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */

public function store(Request $request)
{
    $validated = $request->validate([
        'id_quote' => 'required|exists:quotes,id_quote',
        'date_invoice' => 'required|date',
        'payment_status' => 'required|string',
        'date_payment' => 'nullable|date',
        'currency' => 'required|in:EUR,USD,GBP',
        'payment_terms' => 'nullable|string',
        'total_excl_vat' => 'nullable|numeric',
        'vat_amount' => 'nullable|numeric',
        'total_incl_vat' => 'nullable|numeric',
        'customer_vat_number' => 'nullable|string',
        'customer_contact_person' => 'nullable|string',
        'customer_email' => 'nullable|email',
        'customer_phone' => 'nullable|string',
        'supplier_vat_number' => 'nullable|string',
        'supplier_iso_certification' => 'nullable|string',
        'notes' => 'nullable|string',
    ]);

    DB::transaction(function () use ($validated) {
        // Get quote and customer info
        $quote = Quote::with(['customer', 'quoteProducts.product'])->findOrFail($validated['id_quote']);
        $customer = $quote->customer;

        // Create invoice with snapshot data
        $invoice = Invoice::create([
            'id_quote' => $validated['id_quote'],
            'date_invoice' => $validated['date_invoice'],
            'issue_date' => $validated['date_invoice'],
            'due_date' => $validated['date_payment'] ?? now()->addDays(30),
            'payment_status' => $validated['payment_status'],
            'date_payment' => $validated['date_payment'],
            'id_user' => auth()->id(),
            'created_by' => auth()->id(),
            'currency' => $validated['currency'],
            'payment_terms' => $validated['payment_terms'],
            'total_excl_vat' => $validated['total_excl_vat'] ?? 0,
            'vat_amount' => $validated['vat_amount'] ?? 0,
            'total_incl_vat' => $validated['total_incl_vat'] ?? 0,
            'sub_total' => $validated['total_excl_vat'] ?? 0,
            'tax_total' => $validated['vat_amount'] ?? 0,
            'grand_total' => $validated['total_incl_vat'] ?? 0,
            'customer_name' => $customer->company_name,
            'customer_address' => $customer->address,
            'customer_vat' => $validated['customer_vat_number'] ?? $customer->vat_number,
            'customer_contact_person' => $validated['customer_contact_person'] ?? $customer->contact_name,
            'customer_email' => $validated['customer_email'] ?? $customer->email,
            'customer_phone' => $validated['customer_phone'] ?? $customer->phone,
            'supplier_vat_number' => $validated['supplier_vat_number'],
            'supplier_iso_certification' => $validated['supplier_iso_certification'],
            'notes' => $validated['notes'],
            'status' => 'Draft',
        ]);

        // Ensure the invoice is saved and has an ID
        $invoice->refresh();
        Log::info('Invoice ID after create', ['id_invoice' => $invoice->id_invoice, 'id' => $invoice->id]);
       \Log::info('Invoice PK', ['id_invoice' => $invoice->id_invoice, 'id' => $invoice->id]);
        // Add invoice products with snapshot data from quote
        foreach ($quote->quoteProducts as $quoteProduct) {
            $unitPrice = $quoteProduct->unit_price;
            $quantity = $quoteProduct->quantity;
            $totalHt = $unitPrice * $quantity;
            $vatAmount = $totalHt * 0.21; // 21% VAT
            $lineTotal = $totalHt + $vatAmount;

            // Use $invoice->id_invoice if your primary key is id_invoice, or $invoice->id if it's id
            \DB::table('invoice_quote_product')->insert([
                'invoice_id' => $invoice->id_invoice, // or $invoice->id
                'quote_product_id' => $quoteProduct->id,
                'name' => $quoteProduct->product->name,
                'product_code' => $quoteProduct->product->product_code,
                'description' => $quoteProduct->product->description,
                'unit_price' => $unitPrice,
                'quantity_invoiced' => $quantity,
                'total_ht' => $totalHt,
                'vat_amount' => $vatAmount,
                'reduction' => 0,
                'line_total' => $lineTotal,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        // Calculate and update totals
        $invoice->calculateTotals();
        $invoice->save();
    });

    return redirect()->route('invoices.index')->with('success', 'Invoice created successfully!');
}
    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice)
    {
        $invoice->load([
            'deliveryNote',
            'creator',
            'quoteProducts',
        ]);

        return Inertia::render('Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Invoice $invoice)
    {
        if (! $invoice->isEditable()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'This invoice cannot be edited.');
        }

        $invoice->load([
            'deliveryNote',
            'quoteProducts',
        ]);

        return Inertia::render('Invoices/Edit', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Invoice $invoice)
    {
        if (! $invoice->isEditable()) {
            return redirect()->route('invoices.show', $invoice)
                ->with('error', 'This invoice cannot be edited.');
        }

        $validated = $request->validate([
            'status' => 'required|in:Draft,Unpaid,Partially Paid,Paid,Overdue,Cancelled',
            'due_date' => 'required|date',
            'remarks' => 'nullable|string',
        ]);

        $invoice->update($validated);

        return redirect()->route('invoices.show', $invoice)
            ->with('success', 'Invoice updated successfully!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Invoice $invoice)
    {
        if (! in_array($invoice->status, ['Draft', 'Cancelled'])) {
            return redirect()->route('invoices.index')
                ->with('error', 'Only draft or cancelled invoices can be deleted.');
        }

        $invoice->delete();

        return redirect()->route('invoices.index')
            ->with('success', 'Invoice deleted successfully!');
    }

    /**
     * Get delivery note products for invoice creation
     */
    public function getDeliveryNoteProducts($deliveryNoteId)
    {
        $deliveryNote = DeliveryNote::with([
            'aro.purchaseOrder.quote.quoteProducts.product',
            'products', // This loads the dnp_product pivot table
        ])->findOrFail($deliveryNoteId);

        $products = $deliveryNote->products->map(function ($product) use ($deliveryNote) {
            // Find the corresponding quote product through the relationship chain
            $quoteProduct = null;
            if ($deliveryNote->aro && $deliveryNote->aro->purchaseOrder && $deliveryNote->aro->purchaseOrder->quote) {
                $quoteProduct = $deliveryNote->aro->purchaseOrder->quote->quoteProducts
                    ->where('id_product', $product->id_product)
                    ->first();
            }

            return [
                'quote_product_id' => $quoteProduct ? $quoteProduct->id : null,
                'product_id' => $product->id_product,
                'product_name' => $product->pivot->product_name, // From dnp_product snapshot
                'product_code' => $product->product_code,
                'unit_price' => $product->pivot->product_price, // From dnp_product snapshot
                'quantity_delivered' => $product->pivot->product_quantity_delivered,
                'quantity_shipped' => $product->pivot->quantity_shipped,
                'quantity_available_for_invoice' => $product->pivot->quantity_shipped, // TODO: subtract already invoiced
            ];
        })->filter(function ($product) {
            return $product['quote_product_id'] !== null && $product['quantity_available_for_invoice'] > 0;
        });

        return response()->json([
            'products' => $products->values(),
            'delivery_note' => [
                'id' => $deliveryNote->id_dnp,
                'dnp_number' => $deliveryNote->dnp_number,
                'aro_number' => $deliveryNote->aro->aro_number ?? 'N/A',
                'customer_name' => $deliveryNote->aro->purchaseOrder->customer->company_name ?? 'N/A',
                'status' => $deliveryNote->status,
            ],
        ]);
    }

    /**
     * Get quote products for invoice creation
     */
    public function getQuoteProducts($quoteId)
    {
        $quote = Quote::with(['quoteProducts.product', 'customer'])->findOrFail($quoteId);

        $products = $quote->quoteProducts->map(function ($quoteProduct) {
            return [
                'quote_product_id' => $quoteProduct->id,
                'product_id' => $quoteProduct->id_product,
                'product_name' => $quoteProduct->product->name,
                'product_code' => $quoteProduct->product->product_code,
                'unit_price' => $quoteProduct->unit_price,
                'quantity' => $quoteProduct->quantity,
                'total_line_price' => $quoteProduct->total_line_price,
            ];
        });

        return response()->json([
            'products' => $products,
            'quote' => [
                'id' => $quote->id_quote,
                'quote_number' => $quote->quote_number,
                'customer_name' => $quote->customer->company_name,
                'total_amount' => $quote->total_ttc,
                'currency' => $quote->currency,
            ],
        ]);
    }

    /**
     * Print view for Invoice
     */
    public function printView(Invoice $invoice)
    {
        $invoice->load([
            'deliveryNote.aro.purchaseOrder.customer',
            'quote.customer',
            'products.product',
            'quoteProducts',
            'creator',
        ]);

        // Build products array for print view
        $products = [];
        if ($invoice->quote) {
            // Invoice from quote: use quoteProducts pivot
            foreach ($invoice->quoteProducts as $quoteProduct) {
                $pivot = $quoteProduct->pivot;
                $products[] = [
                    'product_name' => $pivot->product_name ?? $quoteProduct->product->name ?? '',
                    'product_code' => $quoteProduct->product->product_code ?? '',
                    'unit_price' => $pivot->unit_price ?? $quoteProduct->unit_price ?? 0,
                    'quantity' => $pivot->quantity_invoiced ?? $quoteProduct->quantity ?? 0,
                    'total_ht' => $pivot->total_ht ?? 0,
                    'vat_amount' => $pivot->vat_amount ?? 0,
                    'line_total' => $pivot->line_total ?? 0,
                ];
            }
        } elseif ($invoice->deliveryNote) {
            // Invoice from delivery note: use delivery note's products
            foreach ($invoice->deliveryNote->products as $product) {
                $pivot = $product->pivot;
                $products[] = [
                    'product_name' => $pivot->product_name ?? $product->name ?? '',
                    'product_code' => $product->product_code ?? '',
                    'unit_price' => $pivot->product_price ?? 0,
                    'quantity' => $pivot->product_quantity_delivered ?? 0,
                    'total_ht' => ($pivot->product_price ?? 0) * ($pivot->product_quantity_delivered ?? 0),
                    'vat_amount' => 0, // Add VAT logic if needed
                    'line_total' => ($pivot->product_price ?? 0) * ($pivot->product_quantity_delivered ?? 0),
                ];
            }
        }

        return Inertia::render('Invoices/Print', [
            'invoice' => $invoice,
            'products' => $products,
        ]);
    }

    /**
     * Download PDF for Invoice
     */
    public function downloadPdf(Invoice $invoice)
    {
        try {
            $invoice->load([
                'deliveryNote.aro.purchaseOrder.customer',
                'quote.customer',
                'products.product',
                'creator',
            ]);

            $pdf = Pdf::loadView('pdf.invoice', compact('invoice'));

            return $pdf->download('Invoice-'.$invoice->invoice_number.'.pdf');

        } catch (\Exception $e) {
            Log::error('Error generating Invoice PDF: '.$e->getMessage());

            return back()->with('error', 'Error generating PDF. Please try again.');
        }
    }
}
