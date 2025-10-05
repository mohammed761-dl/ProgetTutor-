<?php

// filepath: app/Http/Controllers/CustomerController.php

namespace App\Http\Controllers;

use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class CustomerController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Customer::select(
                'id_customer',
                'company_name',
                'contact_name',
                'email',
                'phone',
                'address',
                'performance_flag',
                'vat_number',
                'created_at'
            );

            // Apply search filters if provided
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('company_name', 'like', "%{$search}%")
                        ->orWhere('contact_name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%")
                        ->orWhere('vat_number', 'like', "%{$search}%");
                });
            }

            // Apply performance flag filter
            if ($request->has('performance')) {
                $query->where('performance_flag', $request->get('performance'));
            }

            // Apply sorting
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            $allowedSortFields = ['company_name', 'contact_name', 'email', 'performance_flag', 'created_at'];

            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Paginate results
            $customers = $query->paginate(10)->withQueryString();

            Log::info('Customers fetched: '.$customers->total());

            return Inertia::render('Customers/Index', [
                'customers' => $customers->items(),
                'filters' => $request->only(['search', 'performance', 'sort', 'direction']),
                'performanceFlags' => ['Always on time', 'Small delays', 'Frequent big delays', 'No payment'],
                'pagination' => [
                    'current_page' => $customers->currentPage(),
                    'last_page' => $customers->lastPage(),
                    'per_page' => $customers->perPage(),
                    'total' => $customers->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching customers: '.$e->getMessage());

            return Inertia::render('Customers/Index', [
                'customers' => [],
                'filters' => $request->only(['search', 'performance', 'sort', 'direction']),
                'performanceFlags' => ['Always on time', 'Small delays', 'Frequent big delays', 'No payment'],
            ]);
        }
    }

    public function create()
    {
        return Inertia::render('Customers/Create', [
            'performanceFlags' => ['Always on time', 'Small delays', 'Frequent big delays', 'No payment'],
        ]);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Creating customer with data: '.json_encode($request->all()));

            $validated = $request->validate([
                'company_name' => 'required|string|max:255',
                'contact_name' => 'required|string|max:255',
                'email' => 'required|string|email|max:255|unique:customers,email',
                'phone' => 'required|string|max:50',
                'address' => 'required|string',
                'performance_flag' => ['required', Rule::in(['Always on time', 'Small delays', 'Frequent big delays', 'No payment'])],
                'vat_number' => 'required|string|max:50',
            ]);

            $customer = Customer::create($validated);

            Log::info('Customer created successfully: '.$customer->company_name);

            return redirect('/Customer')
                ->with('success', 'Customer created successfully.');

        } catch (\Exception $e) {
            Log::error('Error creating customer: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return back()->withErrors(['error' => 'Failed to create customer: '.$e->getMessage()]);
        }
    }

    public function edit(Customer $customer)
    {
        try {
            Log::info('Editing customer: '.$customer->id_customer);

            return Inertia::render('Customers/Edit', [
                'customer' => $customer,
                'performanceFlags' => ['Always on time', 'Small delays', 'Frequent big delays', 'No payment'],
            ]);
        } catch (\Exception $e) {
            Log::error('Error finding customer: '.$e->getMessage());

            return redirect('/Customer')->withErrors(['error' => 'Customer not found.']);
        }
    }

    public function update(Request $request, Customer $customer)
    {
        try {
            Log::info('Updating customer: '.$customer->id_customer);

            $validated = $request->validate([
                'company_name' => 'required|string|max:255',
                'contact_name' => 'required|string|max:255',
                'email' => ['required', 'string', 'email', 'max:255', Rule::unique('customers')->ignore($customer->id_customer, 'id_customer')],
                'phone' => 'required|string|max:50',
                'address' => 'required|string',
                'performance_flag' => ['required', Rule::in(['Always on time', 'Small delays', 'Frequent big delays', 'No payment'])],
                'vat_number' => 'required|string|max:50',
            ]);

            $customer->update($validated);

            Log::info('Customer updated successfully: '.$customer->company_name);

            return redirect('/Customer')
                ->with('success', 'Customer updated successfully.');

        } catch (\Exception $e) {
            Log::error('Error updating customer: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return back()->withErrors(['error' => 'Failed to update customer: '.$e->getMessage()]);
        }
    }

    public function destroy(Customer $customer)
    {
        try {
            // Check if customer has any quotes
            $quotesCount = $customer->quotes()->count();

            if ($quotesCount > 0) {
                $message = "Cannot delete customer '{$customer->company_name}' because they have $quotesCount quote(s)";
                Log::warning($message);

                return back()->withErrors([
                    'error' => $usageMessage.'. Please remove these references first or consider marking the customer as inactive.',
                ]);
            }

            $customerName = $customer->company_name;
            $customer->delete();

            Log::info('Customer deleted: '.$customerName);

            return redirect('/Customer')
                ->with('success', 'Customer deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Error deleting customer: '.$e->getMessage());

            return back()->withErrors(['error' => 'Failed to delete customer: '.$e->getMessage()]);
        }
    }
}
