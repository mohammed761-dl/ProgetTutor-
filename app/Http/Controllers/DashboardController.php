<?php

namespace App\Http\Controllers;

use App\Models\ARO;
use App\Models\Customer;
use App\Models\DeliveryNote;
use App\Models\Invoice;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Quote;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;

class DashboardController extends Controller
{
    /**
     * Admin Dashboard with real data (UNCHANGED - DON'T TOUCH)
     */
    public function adminDashboard()
    {
        try {
            $admin = Auth::guard('admin')->user();

            // ✅ Get real product statistics
            $totalProducts = Product::count();
            $activeProducts = Product::where('status', 'Active')->count();
            $eolProducts = Product::where('status', 'EOL')->count();
            $archivedProducts = Product::where('status', 'Archived')->count();

            // ✅ Get real customer statistics
            $totalCustomers = Customer::count();
            $goodCustomers = Customer::where('performance_flag', 'Always on time')->count();
            $averageCustomers = Customer::whereIn('performance_flag', ['Small delays', 'Frequent big delays'])->count();
            $poorCustomers = Customer::where('performance_flag', 'No payment')->count();

            // ✅ Get real user statistics
            $totalUsers = User::count();
            $ceoUsers = User::where('role', 'CEO')->count();
            $commercialUsers = User::where('role', 'Commercial')->count();
            $viewerUsers = User::where('role', 'Viewer')->count();

            // ✅ Get recent activities (last 10 records from different tables)
            $recentActivities = collect();

            // Recent products (last 5)
            $recentProducts = Product::latest('created_at')->take(5)->get();
            foreach ($recentProducts as $product) {
                $recentActivities->push([
                    'id' => 'product_'.$product->id_product,
                    'action' => 'Product Added',
                    'description' => "New product '{$product->name}' was added to catalog",
                    'date' => $product->created_at,
                    'status' => 'completed',
                    'type' => 'product',
                ]);
            }

            // Recent customers (last 5)
            $recentCustomers = Customer::latest('created_at')->take(5)->get();
            foreach ($recentCustomers as $customer) {
                $recentActivities->push([
                    'id' => 'customer_'.$customer->id_customer,
                    'action' => 'Customer Registration',
                    'description' => "New customer '{$customer->company_name}' was registered",
                    'date' => $customer->created_at,
                    'status' => 'completed',
                    'type' => 'customer',
                ]);
            }

            // Recent users (last 5)
            $recentUsers = User::latest('created_at')->take(5)->get();
            foreach ($recentUsers as $user) {
                $recentActivities->push([
                    'id' => 'user_'.$user->id_user,
                    'action' => 'User Account Created',
                    'description' => "New {$user->role} user '{$user->name}' was created",
                    'date' => $user->created_at,
                    'status' => 'completed',
                    'type' => 'user',
                ]);
            }

            // Sort activities by date (newest first) and take top 10
            $sortedActivities = $recentActivities->sortByDesc('date')->take(10)->values();

            // ✅ Prepare stats object
            $dashboardStats = [
                // Product stats
                'totalProducts' => $totalProducts,
                'activeProducts' => $activeProducts,
                'eolProducts' => $eolProducts,
                'archivedProducts' => $archivedProducts,
                'lowStockProducts' => $eolProducts, // Using EOL as low stock indicator
                'outOfStockProducts' => $archivedProducts, // Using archived as out of stock

                // Customer stats
                'totalCustomers' => $totalCustomers,
                'goodCustomers' => $goodCustomers,
                'averageCustomers' => $averageCustomers,
                'poorCustomers' => $poorCustomers,

                // User stats
                'totalUsers' => $totalUsers,
                'ceoUsers' => $ceoUsers,
                'commercialUsers' => $commercialUsers,
                'viewerUsers' => $viewerUsers,

                // Activities
                'recentActivities' => $sortedActivities,
            ];

            Log::info('Admin dashboard stats generated successfully', [
                'products' => $totalProducts,
                'customers' => $totalCustomers,
                'users' => $totalUsers,
            ]);

            return Inertia::render('Dashboard/AdminDashboard', [
                'admin' => $admin,
                'stats' => $dashboardStats,
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating admin dashboard stats: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            // ✅ Return empty stats in case of error, but still show admin info
            return Inertia::render('Dashboard/AdminDashboard', [
                'admin' => $admin,
                'stats' => [
                    'totalProducts' => 0,
                    'activeProducts' => 0,
                    'eolProducts' => 0,
                    'archivedProducts' => 0,
                    'lowStockProducts' => 0,
                    'outOfStockProducts' => 0,
                    'totalCustomers' => 0,
                    'goodCustomers' => 0,
                    'averageCustomers' => 0,
                    'poorCustomers' => 0,
                    'totalUsers' => 0,
                    'ceoUsers' => 0,
                    'commercialUsers' => 0,
                    'viewerUsers' => 0,
                    'recentActivities' => [],
                ],
            ]);
        }
    }

    /**
     * ✅ USER DASHBOARD - NOW WITH REAL DATA USING QUOTE MODEL
     */
    public function userDashboard()
    {
        try {
            $user = Auth::guard('web')->user();

            // ✅ Debug: Log what we're checking
            Log::info('User Dashboard Data Check:', [
                'customers' => Customer::count(),
                'products' => Product::count(),
                'quotes' => Quote::count(), // ✅ Using Quote model
                'invoices' => Invoice::count(),
                'pos' => PurchaseOrder::count(),
                'aros' => ARO::count(),
                'delivery_notes' => DeliveryNote::count(),
            ]);

            // ✅ Get real statistics from your database
            $totalQuotes = Quote::count(); // ✅ Using Quote model
            $totalInvoices = Invoice::count();
            $totalCustomers = Customer::count();
            $totalProducts = Product::count();

            // ✅ Quote statistics - using proper business logic
            // Pending quotes = quotes without PO (has_po = false)
            $pendingQuotes = Quote::where('has_po', false)->count();

            // Accepted quotes = quotes with PO (has_po = true)
            $acceptedQuotes = Quote::where('has_po', true)->count();

            // Legacy delivery timing stats (separate from business status)
            $sameDayQuotes = Quote::where('status', 'Sent same day')->count();
            $delayedQuotes = Quote::whereIn('status', ['Sent within 2-3 days', 'Sent after 4+ days'])->count();

            // ✅ Financial statistics
            $totalRevenue = Invoice::sum('total_incl_vat') ?? 0;
            $totalQuoteValue = Quote::sum('total_amount') ?? 0; // ✅ Using Quote
            $averageInvoiceAmount = Invoice::avg('total_incl_vat') ?? 0;
            $averageQuoteAmount = Quote::avg('total_amount') ?? 0; // ✅ Using Quote

            // ✅ Monthly revenue data
            $monthlyRevenue = $this->getMonthlyRevenue();

            // ✅ Recent quotes with customer info
            $recentQuotes = Quote::with(['customer']) // ✅ Using Quote model
                ->orderBy('created_at', 'desc')
                ->take(10)
                ->get();

            // ✅ Prepare stats for user dashboard
            $userStats = [
                // Basic counts
                'totalQuotes' => $totalQuotes,
                'totalInvoices' => $totalInvoices,
                'totalCustomers' => $totalCustomers,
                'totalProducts' => $totalProducts,

                // Quote breakdown - using correct business logic
                'pendingQuotes' => $pendingQuotes,    // has_po = false
                'acceptedQuotes' => $acceptedQuotes,  // has_po = true

                // Legacy delivery tracking (for backwards compatibility)
                'sameDayQuotes' => $sameDayQuotes,
                'delayedQuotes' => $delayedQuotes,

                // Financial data
                'totalRevenue' => $totalRevenue,
                'totalQuoteValue' => $totalQuoteValue,
                'averageInvoiceAmount' => $averageInvoiceAmount,
                'averageQuoteAmount' => $averageQuoteAmount,

                // Chart data
                'monthlyRevenue' => $monthlyRevenue,

                // Table data
                'recentQuotes' => $recentQuotes,
            ];

            // ✅ Log the stats for debugging
            Log::info('User Dashboard Stats:', $userStats);

            return Inertia::render('Dashboard/UserDashboard', [
                'user' => $user,
                'stats' => $userStats,
            ]);

        } catch (\Exception $e) {
            Log::error('Error generating user dashboard stats: '.$e->getMessage());
            Log::error('Stack trace: '.$e->getTraceAsString());

            // ✅ Return empty stats in case of error
            return Inertia::render('Dashboard/UserDashboard', [
                'user' => Auth::guard('web')->user(),
                'stats' => $this->getEmptyUserStats(),
            ]);
        }
    }

    /**
     * ✅ Get monthly revenue for the last 12 months
     */
    private function getMonthlyRevenue()
    {
        try {
            // ✅ Get monthly invoice data
            $monthlyData = Invoice::select(
                DB::raw('MONTH(date_invoice) as month'),
                DB::raw('YEAR(date_invoice) as year'),
                DB::raw('SUM(COALESCE(total_incl_vat, 0)) as revenue'),
                DB::raw('COUNT(*) as invoice_count')
            )
                ->where('date_invoice', '>=', Carbon::now()->subMonths(12))
                ->groupBy('year', 'month')
                ->orderBy('year', 'asc')
                ->orderBy('month', 'asc')
                ->get();

            // ✅ Create arrays for the last 12 months
            $months = [];
            $revenue = [];
            $expenses = [];
            $profit = [];

            for ($i = 11; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $months[] = $date->format('M');

                $monthData = $monthlyData->where('month', $date->month)
                    ->where('year', $date->year)
                    ->first();

                $monthRevenue = $monthData ? (float) $monthData->revenue : 0;
                $revenue[] = $monthRevenue;

                // ✅ Estimate expenses as 70% of revenue
                $monthExpenses = $monthRevenue * 0.7;
                $expenses[] = $monthExpenses;

                // ✅ Calculate profit
                $profit[] = $monthRevenue - $monthExpenses;
            }

            return [
                'labels' => $months,
                'revenue' => $revenue,
                'expenses' => $expenses,
                'profit' => $profit,
            ];

        } catch (\Exception $e) {
            Log::error('Monthly Revenue Error: '.$e->getMessage());

            // ✅ Return empty data with proper structure
            return [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'revenue' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                'expenses' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                'profit' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            ];
        }
    }

    /**
     * ✅ Return empty stats structure for user dashboard
     */
    private function getEmptyUserStats()
    {
        return [
            'totalQuotes' => 0,
            'totalInvoices' => 0,
            'totalCustomers' => 0,
            'totalProducts' => 0,

            // Quote status breakdown
            'pendingQuotes' => 0,     // has_po = false
            'acceptedQuotes' => 0,    // has_po = true

            // Legacy delivery tracking
            'sameDayQuotes' => 0,
            'delayedQuotes' => 0,

            // Financial data
            'totalRevenue' => 0,
            'totalQuoteValue' => 0,
            'averageInvoiceAmount' => 0,
            'averageQuoteAmount' => 0,
            'monthlyRevenue' => [
                'labels' => ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                'revenue' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                'expenses' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                'profit' => [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
            ],
            'recentQuotes' => [],
        ];
    }
}
