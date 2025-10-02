<?php

namespace App\Http\Controllers;

use App\Models\ARO;
use App\Models\DeliveryNote;
use App\Models\Product;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DeliveryNoteController extends Controller
{
    public function index(Request $request)
    {
        $query = DeliveryNote::with(['aro.purchaseOrder.customer', 'createdBy']);

        // Add search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('dnp_number', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('aro', function ($aroQuery) use ($search) {
                        $aroQuery->where('aro_number', 'like', "%{$search}%")
                            ->orWhereHas('purchaseOrder', function ($poQuery) use ($search) {
                                $poQuery->where('po_number', 'like', "%{$search}%")
                                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                                        $customerQuery->where('company_name', 'like', "%{$search}%")
                                            ->orWhere('contact_name', 'like', "%{$search}%");
                                    });
                            });
                    })
                    ->orWhereHas('createdBy', function ($userQuery) use ($search) {
                        $userQuery->where('name', 'like', "%{$search}%");
                    });
            });
        }

        // Add status filter
        if ($request->has('status') && $request->status) {
            $query->where('status', $request->status);
        }

        // Add date range filter
        if ($request->has('date_from') && $request->date_from) {
            $query->whereDate('date_delivery', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('date_delivery', '<=', $request->date_to);
        }

        $deliveryNotes = $query->orderBy('created_at', 'desc')
            ->get()
            ->map(function ($dn) {
                return [
                    'id_dn' => $dn->id_dnp,
                    'dn_number' => $dn->dnp_number,
                    'status' => $dn->status ?? $dn->delivery_status,
                    'status_badge' => $dn->getStatusBadge(),
                    'aro_number' => $dn->aro?->aro_number ?? 'No ARO',
                    'customer_name' => $dn->aro?->purchaseOrder?->customer?->company_name ?? 'Unknown Customer',
                    'planned_delivery_date' => $dn->planned_delivery_date,
                    'actual_delivery_date' => $dn->actual_delivery_date,
                    'created_by' => $dn->createdBy->name ?? 'Unknown',
                    'created_at' => $dn->created_at->format('d/m/Y'),
                ];
            });

        return Inertia::render('DeliveryNote/Index', [
            'deliveryNotes' => $deliveryNotes,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
        ]);
    }

    public function create()
    {
        $aros = ARO::with(['purchaseOrder.customer'])
            ->where('status', 'Pending')
            ->orderBy('aro_number')
            ->get()
            ->map(function ($aro) {
                return [
                    'id_aro' => $aro->id_aro,
                    'aro_number' => $aro->aro_number,
                    'customer_name' => $aro->purchaseOrder->customer->company_name,
                    'po_number' => $aro->purchaseOrder->po_number,
                ];
            });

        return Inertia::render('DeliveryNote/Create', [
            'aros' => $aros,
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'id_aro' => 'required|exists:aros,id_aro',
            'date_delivery' => 'required|date',
            'planned_delivery_date' => 'required|date',
            'actual_delivery_date' => 'nullable|date',
            'status' => 'required|in:Pending,Delivered,Partially Delivered,Returned,Cancelled',
            'incoterms' => 'nullable|string|max:20',
            'delivery_address' => 'required|string',
            'packaging_details' => 'nullable|string',
            'remarks' => 'nullable|string',
            // Remove products validation - we get them from ARO
        ]);

        // Add created_by to validated data
        $validated['created_by'] = Auth::user()->id_user;

        // Get the ARO with its relationships
        $aro = ARO::with([
            'purchaseOrder.quote', 
            'products.quoteProduct.product',
            'purchaseOrder.poProducts'
        ])->findOrFail($validated['id_aro']);
        
        $validated['id_po'] = $aro->purchaseOrder->id_po;
        $validated['id_quote'] = $aro->purchaseOrder->quote->id_quote;

        // Create delivery note (DN number will be auto-generated)
        $deliveryNote = DeliveryNote::create($validated);

        if ($aro) {
            // Create DnpProduct records with snapshot data from ARO products
            foreach ($aro->products as $aroProduct) {
                if (!$aroProduct->quoteProduct) continue; // Skip if no quote product
                
                // Find the corresponding PO product
                $poProduct = $aro->purchaseOrder->poProducts()
                    ->where('quote_product_id', $aroProduct->quote_product_id)
                    ->first();
                
                if (!$poProduct) continue; // Skip if no PO product found

                $deliveryNote->products()->create([
                    'aro_product_id' => $aroProduct->id,
                    'po_product_id' => $poProduct->id, // Using actual PO product ID
                    'product_code' => $aroProduct->quoteProduct->product->product_code,
                    'name' => $aroProduct->quoteProduct->product->name,
                    'description' => $aroProduct->quoteProduct->product->description,
                    'technical_specs' => $aroProduct->quoteProduct->product->technical_specs,
                    'quantity_shipped' => $aroProduct->quantity_received ?? 0,
                    'unit_price' => $aroProduct->quoteProduct->unit_price ?? 0,
                ]);
            }
        }

        return redirect()->route('delivery-notes.index')->with('success', 'Delivery Note created successfully!');
    }

    public function show(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load([
            'aro.purchaseOrder.customer',
            'createdBy',
            'products',
        ]);

        return Inertia::render('DeliveryNote/Show', [
            'deliveryNote' => [
                'id_dn' => $deliveryNote->id_dnp,
                'dn_number' => $deliveryNote->dnp_number,
                'id_aro' => $deliveryNote->id_aro,
                'status' => $deliveryNote->status ?? $deliveryNote->delivery_status,
                'status_badge' => $deliveryNote->getStatusBadge(),
                'date_delivery' => $deliveryNote->date_delivery,
                'planned_delivery_date' => $deliveryNote->planned_delivery_date,
                'actual_delivery_date' => $deliveryNote->actual_delivery_date,
                'incoterms' => $deliveryNote->incoterms,
                'delivery_address' => $deliveryNote->delivery_address,
                'packaging_details' => $deliveryNote->packaging_details,
                'remarks' => $deliveryNote->remarks,
                'aro_number' => $deliveryNote->aro?->aro_number ?? 'No ARO',
                'customer_name' => $deliveryNote->aro?->purchaseOrder?->customer?->company_name ?? 'Unknown Customer',
                'po_number' => $deliveryNote->aro?->purchaseOrder?->po_number ?? 'No PO',
                'created_by' => $deliveryNote->createdBy->name ?? 'Unknown',
                'created_at' => $deliveryNote->created_at->format('d/m/Y H:i'),
                'products' => $deliveryNote->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'aro_product_id' => $product->aro_product_id,
                        'product_code' => $product->product_code,
                        'name' => $product->name,
                        'description' => $product->description,
                        'technical_specs' => $product->technical_specs,
                        'quantity_shipped' => $product->quantity_shipped,
                        'unit_price' => $product->unit_price,
                        'total_line_price' => $product->total_line_price,
                        'serial_numbers' => $product->serial_numbers,
                        'tracking_code' => $product->tracking_code,
                    ];
                }),
            ],
        ]);
    }

    public function edit(DeliveryNote $deliveryNote)
    {
        $aros = ARO::with(['purchaseOrder.customer'])
            ->where('status', 'Pending')
            ->orderBy('aro_number')
            ->get()
            ->map(function ($aro) {
                return [
                    'id_aro' => $aro->id_aro,
                    'aro_number' => $aro->aro_number,
                    'customer_name' => $aro->purchaseOrder->customer->company_name,
                    'po_number' => $aro->purchaseOrder->po_number,
                ];
            });

        $deliveryNote->load([
            'aro.purchaseOrder.customer',
            'products',
        ]);

        return Inertia::render('DeliveryNote/Edit', [
            'deliveryNote' => [
                'id_dn' => $deliveryNote->id_dnp,
                'dn_number' => $deliveryNote->dnp_number,
                'id_aro' => $deliveryNote->id_aro,
                'status' => $deliveryNote->status ?? $deliveryNote->delivery_status,
                'date_delivery' => $deliveryNote->date_delivery,
                'planned_delivery_date' => $deliveryNote->planned_delivery_date,
                'actual_delivery_date' => $deliveryNote->actual_delivery_date,
                'incoterms' => $deliveryNote->incoterms,
                'delivery_address' => $deliveryNote->delivery_address,
                'packaging_details' => $deliveryNote->packaging_details,
                'remarks' => $deliveryNote->remarks,
                'products' => $deliveryNote->products->map(function ($product) {
                    return [
                        'id' => $product->id,
                        'product_code' => $product->product_code,
                        'name' => $product->name,
                        'description' => $product->description,
                        'technical_specs' => $product->technical_specs,
                        'quantity_shipped' => $product->quantity_shipped,
                        'unit_price' => $product->unit_price,
                        'total_line_price' => $product->total_line_price,
                        'serial_numbers' => $product->serial_numbers,
                        'tracking_code' => $product->tracking_code,
                    ];
                }),
            ],
            'aros' => $aros,
        ]);
    }

    public function update(Request $request, DeliveryNote $deliveryNote)
    {
        $validated = $request->validate([
            'id_aro' => 'required|exists:aros,id_aro',
            'date_delivery' => 'required|date',
            'planned_delivery_date' => 'required|date',
            'actual_delivery_date' => 'nullable|date',
            'status' => 'required|in:Pending,Delivered,Partially Delivered,Returned,Cancelled',
            'incoterms' => 'nullable|string|max:20',
            'delivery_address' => 'required|string',
            'packaging_details' => 'nullable|string',
            'remarks' => 'nullable|string',
            'products' => 'array',
            'products.*.id' => 'required|exists:dnp_product,id',
            'products.*.quantity_shipped' => 'required|integer|min:0',
            'products.*.serial_numbers' => 'nullable|string',
            'products.*.tracking_code' => 'nullable|string',
        ]);

        // Update delivery note
        $deliveryNote->update($validated);

        // If ARO changed, recreate products from new ARO
        if ($deliveryNote->id_aro != $validated['id_aro']) {
            // Delete existing products
            $deliveryNote->products()->delete();

            // Get products from the new ARO
            $aro = ARO::with(['products.product'])->find($validated['id_aro']);

            if ($aro) {
                // Create new DnpProduct records with snapshot data
                foreach ($aro->products as $aroProduct) {
                    $deliveryNote->products()->create([
                        'aro_product_id' => $aroProduct->id,
                        'product_code' => $aroProduct->product->product_code,
                        'name' => $aroProduct->product->name,
                        'description' => $aroProduct->product->description,
                        'technical_specs' => $aroProduct->product->technical_specs,
                        'quantity_shipped' => $aroProduct->quantity_confirmed,
                        'unit_price' => $aroProduct->unit_price,
                    ]);
                }
            }
        } else {
            // Update existing products
            foreach ($validated['products'] as $productData) {
                $deliveryNote->products()
                    ->where('id', $productData['id'])
                    ->update([
                        'quantity_shipped' => $productData['quantity_shipped'],
                        'serial_numbers' => $productData['serial_numbers'] ?? null,
                        'tracking_code' => $productData['tracking_code'] ?? null,
                    ]);
            }
        }

        return redirect()->route('delivery-notes.index')->with('success', 'Delivery Note updated successfully!');
    }

    public function destroy($id)
    {
        try {
            $deliveryNote = DeliveryNote::findOrFail($id);
            
            // Check if DN is already delivered
            if ($deliveryNote->status === 'Delivered') {
                return back()->with('error', 'Cannot delete delivered Delivery Notes.');
            }

            \DB::transaction(function () use ($deliveryNote) {
                // Delete all product snapshots first
                $deliveryNote->products()->delete();
                
                // Delete the delivery note
                $deliveryNote->delete();

                Log::info('Delivery Note deleted successfully: '.$deliveryNote->dnp_number);
            });

            return redirect()->route('delivery-notes.index')
                ->with('success', 'Delivery Note deleted successfully');

        } catch (\Exception $e) {
            Log::error('Error deleting Delivery Note: '.$e->getMessage(), [
                'id' => $id,
                'trace' => $e->getTraceAsString()
            ]);

            return back()->with('error', 'Failed to delete the delivery note. ' . $e->getMessage());
        }
    }

    public function getAroProducts(ARO $aro)
    {
        $aro->load(['products.product']);

        $products = $aro->products->map(function ($aroProduct) {
            return [
                'id' => $aroProduct->id,
                'product_code' => $aroProduct->product->product_code,
                'name' => $aroProduct->product->name,
                'description' => $aroProduct->product->description,
                'technical_specs' => $aroProduct->product->technical_specs,
                'quantity_confirmed' => $aroProduct->quantity_confirmed,
                'unit_price' => $aroProduct->unit_price,
            ];
        });

        return response()->json([
            'products' => $products,
        ]);
    }

    public function printView(DeliveryNote $deliveryNote)
    {
        $deliveryNote->load(['aro.purchaseOrder.customer', 'products', 'createdBy']);

        $data = [
            'id_dn' => $deliveryNote->id_dnp,
            'dn_number' => $deliveryNote->dnp_number,
            'date_delivery' => $deliveryNote->date_delivery,
            'planned_delivery_date' => $deliveryNote->planned_delivery_date,
            'actual_delivery_date' => $deliveryNote->actual_delivery_date,
            'status' => $deliveryNote->status,
            'incoterms' => $deliveryNote->incoterms,
            'delivery_address' => $deliveryNote->delivery_address,
            'packaging_details' => $deliveryNote->packaging_details,
            'remarks' => $deliveryNote->remarks,
            'customer' => [
                'company_name' => $deliveryNote->aro->purchaseOrder->customer->company_name,
                'contact_name' => $deliveryNote->aro->purchaseOrder->customer->contact_name,
                'address' => $deliveryNote->aro->purchaseOrder->customer->address,
                'phone' => $deliveryNote->aro->purchaseOrder->customer->phone,
                'email' => $deliveryNote->aro->purchaseOrder->customer->email,
            ],
            'created_by' => $deliveryNote->createdBy->name,
            'created_at' => $deliveryNote->created_at->format('d/m/Y H:i'),
            'products' => $deliveryNote->products->map(function ($product) {
                return [
                    'product_code' => $product->product_code,
                    'name' => $product->name,
                    'description' => $product->description,
                    'technical_specs' => $product->technical_specs,
                    'quantity_shipped' => $product->quantity_shipped,
                    'unit_price' => $product->unit_price,
                    'total_line_price' => $product->total_line_price,
                    'serial_numbers' => $product->serial_numbers,
                    'tracking_code' => $product->tracking_code,
                ];
            }),
            'total_amount' => $deliveryNote->products->sum('total_line_price'),
        ];

        return Inertia::render('DeliveryNote/Print', [
            'deliveryNote' => $data,
        ]);
    }

    public function downloadPdf(DeliveryNote $deliveryNote)
    {
        try {
            $deliveryNote->load(['aro.purchaseOrder.customer', 'products', 'createdBy']);

            $data = [
                'dn_number' => $deliveryNote->dnp_number,
                'date_delivery' => $deliveryNote->date_delivery->format('d/m/Y'),
                'planned_delivery_date' => $deliveryNote->planned_delivery_date->format('d/m/Y'),
                'actual_delivery_date' => $deliveryNote->actual_delivery_date ? $deliveryNote->actual_delivery_date->format('d/m/Y') : null,
                'status' => $deliveryNote->status,
                'incoterms' => $deliveryNote->incoterms,
                'delivery_address' => $deliveryNote->delivery_address,
                'packaging_details' => $deliveryNote->packaging_details,
                'remarks' => $deliveryNote->remarks,
                'customer' => [
                    'company_name' => $deliveryNote->aro->purchaseOrder->customer->company_name,
                    'contact_name' => $deliveryNote->aro->purchaseOrder->customer->contact_name,
                    'address' => $deliveryNote->aro->purchaseOrder->customer->address,
                    'phone' => $deliveryNote->aro->purchaseOrder->customer->phone,
                    'email' => $deliveryNote->aro->purchaseOrder->customer->email,
                ],
                'products' => $deliveryNote->products->map(function ($product) {
                    return [
                        'product_code' => $product->product_code,
                        'name' => $product->name,
                        'description' => $product->description,
                        'technical_specs' => $product->technical_specs,
                        'quantity_shipped' => $product->quantity_shipped,
                        'unit_price' => number_format($product->unit_price, 2),
                        'total_line_price' => number_format($product->total_line_price, 2),
                        'serial_numbers' => $product->serial_numbers,
                        'tracking_code' => $product->tracking_code,
                    ];
                }),
                'total_amount' => number_format($deliveryNote->products->sum('total_line_price'), 2),
                'created_by' => $deliveryNote->createdBy->name,
                'created_at' => $deliveryNote->created_at->format('d/m/Y H:i'),
            ];

            $pdf = Pdf::loadView('pdf.delivery-note', $data);
            
            return $pdf->download('DeliveryNote-'.$deliveryNote->dnp_number.'.pdf');

        } catch (\Exception $e) {
            Log::error('Error generating Delivery Note PDF: '.$e->getMessage());
            return back()->with('error', 'Error generating PDF. Please try again.');
        }
    }
}
