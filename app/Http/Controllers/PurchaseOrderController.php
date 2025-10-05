<?php

// filepath: app/Http/Controllers/PurchaseOrderController.php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\PoCustomer;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class PurchaseOrderController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $query = PurchaseOrder::with(['customer', 'creator']);

        // Add search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('po_number', 'like', "%{$search}%")
                    ->orWhere('status', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('company_name', 'like', "%{$search}%")
                            ->orWhere('contact_name', 'like', "%{$search}%");
                    })
                    ->orWhereHas('creator', function ($userQuery) use ($search) {
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
            $query->whereDate('created_at', '>=', $request->date_from);
        }
        if ($request->has('date_to') && $request->date_to) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $purchaseOrders = $query->orderBy('created_at', 'desc')->get();

        return Inertia::render('PurchaseOrders/Index', [
            'purchaseOrders' => $purchaseOrders,
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
            ],
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $customers = Customer::orderBy('company_name')->get();
        $users = User::orderBy('name')->get();
        $products = Product::where('status', 'Active')->orderBy('name')->get();
        $quotes = Quote::with('customer')->orderBy('quote_number')->get(); // Add quotes back

        return Inertia::render('PurchaseOrders/Create', [
            'customers' => $customers,
            'users' => $users,
            'products' => $products,
            'quotes' => $quotes, // Include quotes
            'currentUser' => Auth::user(), // Add current user
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'id_customer' => 'required|exists:customers,id_customer',
                'id_quote' => 'nullable|exists:quotes,id_quote', // Quote is optional
                'status' => 'required|in:Pending,Approved,Delivered,Cancelled',
                'planned_delivery_date' => 'nullable|date',
                'actual_delivery_date' => 'nullable|date',
                'remarks' => 'nullable|string',
                'po_pdf' => 'required|file|mimes:pdf|max:10240', // Required PDF file, max 10MB
            ]);
            // Create the purchase order (po_number auto-generated, created_by = current user)
            $purchaseOrder = PurchaseOrder::create([
                'id_customer' => $validated['id_customer'],
                'id_quote' => $validated['id_quote'], // Include quote reference
                'created_by' => Auth::id(), // Use current user ID
                'status' => $validated['status'],
                'planned_delivery_date' => $validated['planned_delivery_date'],
                'actual_delivery_date' => $validated['actual_delivery_date'],
                'remarks' => $validated['remarks'],
            ]);

            // Handle PDF upload - rename file to PO number
            if ($request->hasFile('po_pdf')) {
                $file = $request->file('po_pdf');
                $filename = $purchaseOrder->po_number.'.pdf';
                // Store in storage/app/public/pdf directory using the 'public' disk
                $file->storeAs('pdf', $filename, 'public');
                // Save only the filename in the database
                $purchaseOrder->update(['pdf_path' => $filename]);
                Log::info('PO PDF file stored successfully', [
                    'po_number' => $purchaseOrder->po_number,
                    'path' => './storage/app/public/pdf/'.$filename,
                ]);
            } else {
                throw new \Exception('PO PDF file is required.');
            }

            // Create customer snapshot
            $customer = Customer::find($validated['id_customer']);
            PoCustomer::create([
                'id_po' => $purchaseOrder->id_po,
                'id_customer' => $customer->id_customer,
                'company_name' => $customer->company_name,
                'contact_name' => $customer->contact_name,
                'email' => $customer->email,
                'phone' => $customer->phone,
                'address' => $customer->address,
                'vat_number' => $customer->vat_number,
                'performance_flag' => $customer->performance_flag,
            ]);

            return redirect()->route('po.index')->with('success', 'Purchase Order created successfully!');

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (QueryException $e) {
            Log::error('Database error creating purchase order: '.$e->getMessage());

            return back()->with('error', 'Unable to create purchase order. Please try again.')->withInput();
        } catch (\Exception $e) {
            Log::error('Error creating purchase order: '.$e->getMessage());

            return back()->with('error', 'An unexpected error occurred. Please try again.')->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(PurchaseOrder $purchaseOrder)
    {
        $purchaseOrder->load(['customer', 'creator', 'products']);

        return Inertia::render('PurchaseOrders/Show', [
            'purchaseOrder' => $purchaseOrder,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(PurchaseOrder $purchaseOrder)
    {
        $customers = Customer::orderBy('company_name')->get();
        $users = User::orderBy('name')->get();
        $products = Product::where('status', 'Active')->orderBy('name')->get();
        $quotes = Quote::with('customer')->orderBy('quote_number')->get(); // Add quotes back

        $purchaseOrder->load(['customer', 'creator', 'products', 'quote']);

        return Inertia::render('PurchaseOrders/Edit', [
            'purchaseOrder' => $purchaseOrder,
            'customers' => $customers,
            'users' => $users,
            'products' => $products,
            'quotes' => $quotes, // Include quotes
            'currentUser' => Auth::user(), // Add current user
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, PurchaseOrder $purchaseOrder)
    {
        try {
            $validated = $request->validate([
                'id_customer' => 'required|exists:customers,id_customer',
                'id_quote' => 'nullable|exists:quotes,id_quote', // Quote is optional
                'status' => 'required|in:Pending,Approved,Delivered,Cancelled',
                'planned_delivery_date' => 'nullable|date',
                'actual_delivery_date' => 'nullable|date',
                'remarks' => 'nullable|string',
            ]);

            // Update the purchase order (created_by and po_number cannot be changed)
            $purchaseOrder->update([
                'id_customer' => $validated['id_customer'],
                'id_quote' => $validated['id_quote'],
                'status' => $validated['status'],
                'planned_delivery_date' => $validated['planned_delivery_date'],
                'actual_delivery_date' => $validated['actual_delivery_date'],
                'remarks' => $validated['remarks'],
            ]);

            // Update customer snapshot
            $customer = Customer::find($validated['id_customer']);
            $purchaseOrder->poCustomer()->updateOrCreate(
                ['id_po' => $purchaseOrder->id_po],
                [
                    'id_customer' => $customer->id_customer,
                    'company_name' => $customer->company_name,
                    'contact_name' => $customer->contact_name,
                    'email' => $customer->email,
                    'phone' => $customer->phone,
                    'address' => $customer->address,
                    'vat_number' => $customer->vat_number,
                    'performance_flag' => $customer->performance_flag,
                ]
            );

            return redirect()->route('po.index')->with('success', 'Purchase Order updated successfully!');

        } catch (ValidationException $e) {
            return back()->withErrors($e->errors())->withInput();
        } catch (\Exception $e) {
            Log::error('Error updating purchase order: '.$e->getMessage());

            return back()->with('error', 'An unexpected error occurred. Please try again.')->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(PurchaseOrder $purchaseOrder)
    {
        try {
            $pdfPath = $purchaseOrder->pdf_path; // Save before delete
            $poNumber = $purchaseOrder->po_number;

            \DB::transaction(function () use ($purchaseOrder) {
                // Delete associated records first
                if ($purchaseOrder->poCustomer) {
                    $purchaseOrder->poCustomer->delete();
                }
                $purchaseOrder->products()->detach();
                // Finally delete the purchase order
                $purchaseOrder->delete();
            });

            // Delete the PDF file if it exists (after DB delete, using saved path)
            if ($pdfPath) {
                // Always use ./storage/app/public/pdf/PO_NUMBER.pdf
                $filePath = storage_path('app/public/pdf/'.$pdfPath);
                if (file_exists($filePath)) {
                    unlink($filePath);
                    Log::info('PO PDF file deleted', [
                        'po_number' => $poNumber,
                        'file_path' => $filePath,
                    ]);
                }
            }

            return redirect()->route('po.index')
                ->with('success', 'Purchase Order and related data deleted successfully!');

        } catch (\Exception $e) {
            Log::error('Error deleting purchase order: '.$e->getMessage(), [
                'po_number' => $purchaseOrder->po_number,
                'trace' => $e->getTraceAsString(),
            ]);

            return back()->with('error', 'Error deleting Purchase Order. Please try again.');
        }
    }

    /**
     * Download the stored PDF file for a purchase order
     */
    public function downloadPdf(PurchaseOrder $purchaseOrder)
    {
        try {
            if (! $purchaseOrder->pdf_path) {
                throw new \Exception('No PDF file found for this Purchase Order.');
            }

            // Always use ./storage/app/public/pdf/PO_NUMBER.pdf
            $filePath = storage_path('app/public/pdf/'.$purchaseOrder->pdf_path);

            if (! file_exists($filePath)) {
                throw new \Exception('PDF file not found on disk.');
            }

            Log::info('Serving PO PDF file', [
                'po_number' => $purchaseOrder->po_number,
                'file_path' => $filePath,
            ]);

            return response()->download($filePath, $purchaseOrder->po_number.'.pdf', [
                'Content-Type' => 'application/pdf',
            ]);

        } catch (\Exception $e) {
            Log::error('Error serving PO PDF: '.$e->getMessage(), [
                'po_number' => $purchaseOrder->po_number,
                'pdf_path' => $purchaseOrder->pdf_path ?? 'not set',
            ]);

            return back()->with('error', $e->getMessage());
        }
    }
}
