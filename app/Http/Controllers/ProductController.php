<?php

// filepath: app/Http/Controllers/ProductController.php

namespace App\Http\Controllers;

use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class ProductController extends Controller
{
    public function index(Request $request)
    {
        try {
            $query = Product::select(
                'id_product',
                'product_code',
                'name',
                'description',
                'technical_specs',
                'commercial_terms',
                'payment_terms',
                'image_url',
                'min_delivery_day',
                'max_delivery_day',
                'availability_yrs',
                'status',
                'unit_price',
                'created_at'
            );

            // Apply search filters if provided
            if ($request->has('search')) {
                $search = $request->get('search');
                $query->where(function ($q) use ($search) {
                    $q->where('product_code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            }

            // Apply status filter
            if ($request->has('status')) {
                $query->where('status', $request->get('status'));
            }

            // Apply price range filter
            if ($request->has('min_price')) {
                $query->where('unit_price', '>=', $request->get('min_price'));
            }
            if ($request->has('max_price')) {
                $query->where('unit_price', '<=', $request->get('max_price'));
            }

            // Apply delivery days filter
            if ($request->has('max_delivery')) {
                $query->where('max_delivery_day', '<=', $request->get('max_delivery'));
            }

            // Apply sorting
            $sortField = $request->get('sort', 'created_at');
            $sortDirection = $request->get('direction', 'desc');
            $allowedSortFields = ['product_code', 'name', 'status', 'unit_price', 'created_at'];

            if (in_array($sortField, $allowedSortFields)) {
                $query->orderBy($sortField, $sortDirection);
            }

            // Paginate results
            $products = $query->paginate(10)->withQueryString();

            Log::info('Products fetched: '.$products->total());

            return Inertia::render('Products/Index', [
                'products' => $products->items(),
                'filters' => $request->only(['search', 'status', 'min_price', 'max_price', 'max_delivery', 'sort', 'direction']),
                'statuses' => ['Active', 'EOL', 'Archived'],
                'pagination' => [
                    'current_page' => $products->currentPage(),
                    'last_page' => $products->lastPage(),
                    'per_page' => $products->perPage(),
                    'total' => $products->total(),
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error fetching products: '.$e->getMessage());

            return Inertia::render('Products/Index', [
                'products' => [],
                'filters' => $request->only(['search', 'status', 'min_price', 'max_price', 'max_delivery', 'sort', 'direction']),
                'statuses' => ['Active', 'EOL', 'Archived'],
                'pagination' => [
                    'current_page' => 1,
                    'last_page' => 1,
                    'per_page' => 10,
                    'total' => 0,
                ],
            ]);
        }
    }

    public function create()
    {
        return Inertia::render('Products/Create', [
            'statuses' => ['Active', 'EOL', 'Archived'], // ✅ Match your migration enum
        ]);
    }

    public function store(Request $request)
    {
        try {
            Log::info('Creating product with data: '.json_encode($request->all()));

            $validated = $request->validate([
                'product_code' => 'required|string|max:100|unique:products,product_code',
                'name' => 'required|string|max:255',
                'description' => 'required|string', // ✅ Required in migration
                'technical_specs' => 'required|string', // ✅ Required in migration
                'commercial_terms' => 'required|string', // ✅ Required in migration
                'payment_terms' => 'required|string', // ✅ Required in migration
                'image_url' => 'nullable|url',
                'min_delivery_day' => 'required|integer|min:1',
                'max_delivery_day' => 'required|integer|min:1|gte:min_delivery_day',
                'availability_yrs' => 'required|integer|min:1',
                'status' => ['required', Rule::in(['Active', 'EOL', 'Archived'])], // ✅ Match your migration enum
                'unit_price' => 'required|numeric|min:0', // Add unit_price validation
            ]);

            $product = Product::create($validated);

            Log::info('Product created successfully: '.$product->name);

            return redirect('/Product')
                ->with('success', 'Product created successfully.');

        } catch (\Exception $e) {
            Log::error('Error creating product: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return back()->withErrors(['error' => 'Failed to create product: '.$e->getMessage()]);
        }
    }

    public function edit(Product $product)
    {
        try {
            Log::info('Editing product: '.$product->id_product);

            return Inertia::render('Products/Edit', [
                'product' => $product,
                'statuses' => ['Active', 'EOL', 'Archived'], // ✅ Match your migration enum
            ]);
        } catch (\Exception $e) {
            Log::error('Error finding product: '.$e->getMessage());

            return redirect('/Product')->withErrors(['error' => 'Product not found.']);
        }
    }

    public function update(Request $request, Product $product)
    {
        try {
            Log::info('Updating product: '.$product->id_product);

            $validated = $request->validate([
                'product_code' => ['required', 'string', 'max:100', Rule::unique('products')->ignore($product->id_product, 'id_product')],
                'name' => 'required|string|max:255',
                'description' => 'required|string', // ✅ Required in migration
                'technical_specs' => 'required|string', // ✅ Required in migration
                'commercial_terms' => 'required|string', // ✅ Required in migration
                'payment_terms' => 'required|string', // ✅ Required in migration
                'image_url' => 'nullable|url',
                'min_delivery_day' => 'required|integer|min:1',
                'max_delivery_day' => 'required|integer|min:1|gte:min_delivery_day',
                'availability_yrs' => 'required|integer|min:1',
                'status' => ['required', Rule::in(['Active', 'EOL', 'Archived'])], // ✅ Match your migration enum
                'unit_price' => 'required|numeric|min:0', // Add unit_price validation
            ]);

            $product->update($validated);

            Log::info('Product updated successfully: '.$product->name);

            return redirect('/Product')
                ->with('success', 'Product updated successfully.');

        } catch (\Exception $e) {
            Log::error('Error updating product: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            return back()->withErrors(['error' => 'Failed to update product: '.$e->getMessage()]);
        }
    }

    public function destroy(Product $product)
    {
        try {
            // Check if product is being used in quotes
            $quotesCount = $product->quotes()->count();

            if ($quotesCount > 0) {
                $message = "Cannot delete product '{$product->name}' because it is used in $quotesCount quote(s)";
                Log::warning($message);

                return back()->withErrors([
                    'error' => $usageMessage.'. Please remove these references first or consider archiving the product instead.',
                ]);
            }

            $productName = $product->name;
            $product->delete();

            Log::info('Product deleted: '.$productName);

            return redirect('/Product')
                ->with('success', 'Product deleted successfully.');

        } catch (\Exception $e) {
            Log::error('Error deleting product: '.$e->getMessage());

            return back()->withErrors(['error' => 'Failed to delete product: '.$e->getMessage()]);
        }
    }
}
