<?php

namespace App\Http\Controllers;

use App\Models\ARO;
use App\Models\AroProduct;
use App\Models\PurchaseOrder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

/**
 * ARO (Acknowledgment of Receipt Order) Controller
 *
 * Handles all ARO-related operations including:
 * - Creating and managing AROs
 * - Linking AROs to Purchase Orders
 * - Generating print views and PDFs
 * - Tracking product receipts
 */
class AROController extends Controller
{
    /**
     * Display a listing of AROs with filtering and search capabilities
     */
    public function index(Request $request)
    {
        $query = ARO::with(['purchaseOrder.customer', 'creator'])
            ->orderBy('created_at', 'desc');

        // Apply search filter
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('aro_number', 'like', "%{$search}%")
                    ->orWhereHas('purchaseOrder', function ($pq) use ($search) {
                        $pq->where('po_number', 'like', "%{$search}%");
                    })
                    ->orWhereHas('purchaseOrder.customer', function ($cq) use ($search) {
                        $cq->where('company_name', 'like', "%{$search}%");
                    });
            });
        }

        // Apply status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Apply date range filters
        if ($request->filled('date_from')) {
            $query->where('date_aro', '>=', $request->date_from);
        }
        if ($request->filled('date_to')) {
            $query->where('date_aro', '<=', $request->date_to);
        }

        $aros = $query->paginate(15)->withQueryString();

        return Inertia::render('ARO/Index', [
            'aros' => $aros,
            'filters' => $request->only(['search', 'status', 'date_from', 'date_to']),
        ]);
    }

    /**
     * Show the form for creating a new ARO
     */
    public function create()
    {
        // Get all purchase orders that can have AROs created
        $purchaseOrders = PurchaseOrder::with(['customer', 'quote.customer', 'quote.quoteProducts', 'products'])
            ->where('status', '!=', 'Cancelled')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('ARO/Create', [
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    /**
     * Store a newly created ARO in storage
     */
    public function store(Request $request)
    {
        $request->validate([
            'id_po' => 'required|exists:purchase_orders,id_po',
            'date_aro' => 'required|date',
            'status' => 'required|in:Pending,Delivered,Partially Delivered,Cancelled',
            'remarks' => 'nullable|string|max:1000',
            'products' => 'required|array|min:1',
            'products.*.quote_product_id' => 'required|exists:quote_products,id',
            'products.*.quantity_received' => 'required|integer|min:0',
            'products.*.remarks' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Create ARO
            $aro = ARO::create([
                'id_po' => $request->id_po,
                'date_aro' => $request->date_aro,
                'status' => $request->status,
                'remarks' => $request->remarks,
                'created_by' => Auth::id(),
            ]);

            // Create ARO products
            foreach ($request->products as $product) {
                AroProduct::create([
                    'id_aro' => $aro->id_aro,
                    'quote_product_id' => $product['quote_product_id'],
                    'quantity_received' => $product['quantity_received'],
                    'remarks' => $product['remarks'] ?? null,
                ]);
            }

            // Update purchase order status if all products delivered
            $this->updatePurchaseOrderStatus($aro->purchaseOrder);

            DB::commit();

            return redirect()->route('aro.index')
                ->with('success', 'ARO created successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error creating ARO: '.$e->getMessage());

            return back()->withInput()
                ->with('error', 'Error creating ARO. Please try again.');
        }
    }

    /**
     * Display the specified ARO
     */
    public function show(ARO $aro)
    {
        $aro->load([
            'purchaseOrder.customer',
            'purchaseOrder.poProducts.quoteProduct',
            'creator',
            'aroProducts',
        ]);

        return Inertia::render('ARO/Show', [
            'aro' => $aro,
        ]);
    }

    /**
     * Show the form for editing the specified ARO
     */
    public function edit(ARO $aro)
    {
        $aro->load([
            'purchaseOrder.customer',
            'purchaseOrder.poProducts.quoteProduct',
            'creator',
            'aroProducts',
        ]);

        $purchaseOrders = PurchaseOrder::with(['customer', 'quote.customer'])
            ->where('status', '!=', 'Cancelled')
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('ARO/Edit', [
            'aro' => $aro,
            'purchaseOrders' => $purchaseOrders,
        ]);
    }

    /**
     * Update the specified ARO in storage
     */
    public function update(Request $request, ARO $aro)
    {
        $request->validate([
            'id_po' => 'required|exists:purchase_orders,id_po',
            'date_aro' => 'required|date',
            'status' => 'required|in:Pending,Delivered,Partially Delivered,Cancelled',
            'remarks' => 'nullable|string|max:1000',
            'products' => 'required|array|min:1',
            'products.*.quote_product_id' => 'required|exists:quote_products,id',
            'products.*.quantity_received' => 'required|integer|min:0',
            'products.*.remarks' => 'nullable|string|max:500',
        ]);

        try {
            DB::beginTransaction();

            // Update ARO
            $aro->update([
                'id_po' => $request->id_po,
                'date_aro' => $request->date_aro,
                'status' => $request->status,
                'remarks' => $request->remarks,
            ]);

            // Delete existing ARO products and recreate
            $aro->aroProducts()->delete();

            // Create new ARO products
            foreach ($request->products as $product) {
                AroProduct::create([
                    'id_aro' => $aro->id_aro,
                    'quote_product_id' => $product['quote_product_id'],
                    'quantity_received' => $product['quantity_received'],
                    'remarks' => $product['remarks'] ?? null,
                ]);
            }

            // Update purchase order status
            $this->updatePurchaseOrderStatus($aro->purchaseOrder);

            DB::commit();

            return redirect()->route('aro.show', $aro)
                ->with('success', 'ARO updated successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error updating ARO: '.$e->getMessage());

            return back()->withInput()
                ->with('error', 'Error updating ARO. Please try again.');
        }
    }

    /**
     * Remove the specified ARO from storage
     */
    public function destroy(ARO $aro)
    {
        try {
            DB::beginTransaction();

            // Delete ARO products first
            $aro->aroProducts()->delete();

            // Delete ARO
            $aro->delete();

            // Update purchase order status
            $this->updatePurchaseOrderStatus($aro->purchaseOrder);

            DB::commit();

            return redirect()->route('aro.index')
                ->with('success', 'ARO deleted successfully.');

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error('Error deleting ARO: '.$e->getMessage());

            return back()->with('error', 'Error deleting ARO. Please try again.');
        }
    }

    /**
     * Generate PDF for the specified ARO
     */
    public function generatePDF(ARO $aro)
    {
        try {
            $aro->load([
                'purchaseOrder.customer',
                'purchaseOrder.poProducts.quoteProduct',
                'creator',
                'aroProducts',
            ]);

            // Format data for PDF generation
            $formattedAro = $this->formatAroForPrint($aro);

            // Generate PDF using your preferred library
            // Example: return PDF::loadView('pdf.aro', $formattedAro)->download("ARO-{$aro->aro_number}.pdf");

            return response()->json(['message' => 'PDF generation not implemented yet']);

        } catch (\Exception $e) {
            Log::error('Error generating ARO PDF: '.$e->getMessage());

            return back()->with('error', 'Error generating PDF. Please try again.');
        }
    }

    /**
     * Display the print view for the specified ARO
     */
    public function printView(ARO $aro)
    {
        try {
            // Load all necessary relationships with proper eager loading
            $aro->load([
                'purchaseOrder.customer',
                'purchaseOrder.poProducts.quoteProduct',
                'creator',
                'aroProducts',
            ]);

            // Format the data for printing
            $formattedAro = $this->formatAroForPrint($aro);

            return Inertia::render('ARO/Print', [
                'aro' => $formattedAro,
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating ARO print view: '.$e->getMessage());

            return back()->with('error', 'Error generating print view. Please try again.');
        }
    }

    /**
     * Update purchase order status based on ARO delivery status
     */
    private function updatePurchaseOrderStatus(PurchaseOrder $purchaseOrder)
    {
        $aros = $purchaseOrder->aros;

        if ($aros->isEmpty()) {
            return;
        }

        $totalProducts = $purchaseOrder->poProducts->count();
        $deliveredProducts = 0;

        foreach ($aros as $aro) {
            foreach ($aro->aroProducts as $aroProduct) {
                if ($aroProduct->quantity_received > 0) {
                    $deliveredProducts++;
                }
            }
        }

        if ($deliveredProducts >= $totalProducts) {
            $purchaseOrder->update(['status' => 'Delivered']);
        } elseif ($deliveredProducts > 0) {
            $purchaseOrder->update(['status' => 'Partially Delivered']);
        }
    }

    /**
     * Format ARO data for print/PDF generation
     */
    private function formatAroForPrint(ARO $aro)
    {
        return [
            'id_aro' => $aro->id_aro,
            'aro_number' => $aro->aro_number,
            'date_aro' => $aro->date_aro,
            'status' => $aro->status,
            'remarks' => $aro->remarks,
            'creator' => [
                'name' => $aro->creator->name ?? null,
                'title' => $aro->creator->title ?? null,
            ],
            'purchase_order' => [
                'po_number' => $aro->purchaseOrder->po_number,
                'status' => $aro->purchaseOrder->status,
                'customer' => [
                    'company_name' => $aro->purchaseOrder->customer->company_name,
                    'contact_name' => $aro->purchaseOrder->customer->contact_name,
                    'address' => $aro->purchaseOrder->customer->address,
                    'city' => $aro->purchaseOrder->customer->city,
                    'postal_code' => $aro->purchaseOrder->customer->postal_code,
                    'country' => $aro->purchaseOrder->customer->country,
                    'email' => $aro->purchaseOrder->customer->email,
                    'phone' => $aro->purchaseOrder->customer->phone,
                ],
                'products' => $aro->purchaseOrder->poProducts->map(function ($poProduct) use ($aro) {
                    $aroProduct = $aro->aroProducts->firstWhere('quote_product_id', $poProduct->quote_product_id);
                    $quoteProduct = $poProduct->quoteProduct;

                    // Handle case where quoteProduct might be null
                    if (! $quoteProduct) {
                        return [
                            'id' => null,
                            'product_code' => $poProduct->product_code ?? 'N/A',
                            'name' => $poProduct->name ?? 'N/A',
                            'description' => $poProduct->description ?? 'N/A',
                            'quantity' => $poProduct->quantity ?? 0,
                            'unit_price' => $poProduct->unit_price ?? 0,
                            'quantity_received' => $aroProduct->quantity_received ?? 0,
                            'remarks' => $aroProduct->remarks ?? null,
                        ];
                    }

                    return [
                        'id' => $quoteProduct->id_product ?? null,
                        'product_code' => $quoteProduct->product_code ?? $poProduct->product_code ?? 'N/A',
                        'name' => $quoteProduct->name ?? $poProduct->name ?? 'N/A',
                        'description' => $quoteProduct->description ?? $poProduct->description ?? 'N/A',
                        'quantity' => $poProduct->quantity ?? 0,
                        'unit_price' => $poProduct->unit_price ?? 0,
                        'quantity_received' => $aroProduct->quantity_received ?? 0,
                        'remarks' => $aroProduct->remarks ?? null,
                    ];
                })->toArray(),
            ],
        ];
    }
}
