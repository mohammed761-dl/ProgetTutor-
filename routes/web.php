<?php

// filepath: routes/web.php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\AroController;
use App\Http\Controllers\CsrfController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\CustomerController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\DocumentController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\InvoiceController;
use App\Http\Controllers\InvoiceProductController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\QuoteController;
use App\Http\Controllers\QuoteProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::get('/', [HomeController::class, 'index']);

// CSRF Token route (doesn't require CSRF verification)
Route::get('/csrf-token', [CsrfController::class, 'token'])->withoutMiddleware(['web']);

// Auth for user (public routes)
Route::get('/login', [AuthController::class, 'UserLogin'])->name('user.login');
Route::post('/login', [AuthController::class, 'UserstoreLogin']);
// Route::get("/signup", [AuthController::class, "Usersignup"])->name('user.signup');
// Route::post("/signup", [AuthController::class, "UserstoreSignup"])->name('user.store-signup');
Route::post('/user-logout', [AuthController::class, 'Userlogout'])->name('user.logout');

// Auth for admin (public routes)
Route::middleware(['web'])->group(function () {
    Route::get('/serp-admin-login', [AuthController::class, 'AdminLogin'])->name('serp-admin-login');
    Route::post('/serp-admin-login', [AuthController::class, 'AdminstoreLogin']);
});

/* --------------Protected Admin Routes------------------ */
Route::middleware(['web', 'admin'])->group(function () {
    // Admin Logout
    Route::post('/admin-logout', [AuthController::class, 'AdminLogout'])->name('admin.logout');

    // Admin Dashboard
    Route::get('/admin/dashboard', [DashboardController::class, 'adminDashboard'])->name('admin.dashboard');

    // Products
    Route::get('/Product', [ProductController::class, 'index'])->name('products.index');
    Route::get('/Product/create', [ProductController::class, 'create'])->name('products.create');
    Route::post('/Product', [ProductController::class, 'store'])->name('products.store');
    Route::get('/Product/{product}', [ProductController::class, 'show'])->name('products.show');
    Route::get('/Product/{product}/edit', [ProductController::class, 'edit'])->name('products.edit');
    Route::put('/Product/{product}', [ProductController::class, 'update'])->name('products.update');
    Route::delete('/Product/{product}', [ProductController::class, 'destroy'])->name('products.destroy');
    Route::delete('/Product/bulk-destroy', [ProductController::class, 'bulkDestroy'])->name('products.bulk-destroy');

    // Customer
    Route::get('/Customer', [CustomerController::class, 'index'])->name('customers.index');
    Route::get('/Customer/create', [CustomerController::class, 'create'])->name('customers.create');
    Route::post('/Customer', [CustomerController::class, 'store'])->name('customers.store');
    Route::get('/Customer/{customer}', [CustomerController::class, 'show'])->name('customers.show');
    Route::get('/Customer/{customer}/edit', [CustomerController::class, 'edit'])->name('customers.edit');
    Route::put('/Customer/{customer}', [CustomerController::class, 'update'])->name('customers.update');
    Route::delete('/Customer/{customer}', [CustomerController::class, 'destroy'])->name('customers.destroy');

    // Users
    Route::get('/User', [UserController::class, 'index'])->name('users.index');
    Route::get('/User/create', [UserController::class, 'create'])->name('users.create');
    Route::post('/User', [UserController::class, 'store'])->name('users.store');
    Route::get('/User/{user}', [UserController::class, 'show'])->name('users.show');
    Route::get('/User/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::put('/User/{user}', [UserController::class, 'update'])->name('users.update');
    Route::delete('/User/{user}', [UserController::class, 'destroy'])->name('users.destroy');
    Route::patch('/User/{user}/toggle-status', [UserController::class, 'toggleStatus'])->name('users.toggle-status');
    Route::delete('/User/bulk-destroy', [UserController::class, 'bulkDestroy'])->name('users.bulk-destroy');

    // Admin Profile
    Route::get('/Profile', [AdminController::class, 'profile'])->name('admin.profile');
    Route::put('/serp-admin/profile', [AdminController::class, 'updateProfile'])->name('admin.update-profile');
    Route::put('/serp-admin/change-password', [AdminController::class, 'changePassword'])->name('admin.change-password');
    // Add this line for Company Info update
    Route::put('/serp-admin/company-info', [AdminController::class, 'updateCompanyInfo'])->name('admin.company.update');

});

/* --------------Protected User Routes------------------ */
Route::middleware(['auth'])->group(function () {
    // User Logout
    Route::post('/logout', [AuthController::class, 'Userlogout'])->name('logout');

    // User Dashboard
    Route::get('/dashboard', [DashboardController::class, 'userDashboard'])->name('dashboard');

    // Quotes
    Route::get('/Quote', [QuoteController::class, 'index'])->name('quotes.index');
    Route::get('/Quote/create', [QuoteController::class, 'create'])->name('quotes.create');
    Route::post('/Quote', [QuoteController::class, 'store'])->name('quotes.store');
    Route::get('/Quote/{quote}', [QuoteController::class, 'show'])->name('quotes.show');
    Route::get('/Quote/{quote}/edit', [QuoteController::class, 'edit'])->name('quotes.edit'); // ✅ This line
    Route::put('/Quote/{quote}', [QuoteController::class, 'update'])->name('quotes.update');
    Route::delete('/Quote/{quote}', [QuoteController::class, 'destroy'])->name('quotes.destroy');
    Route::get('/Quote/{quote}/pdf', [QuoteController::class, 'downloadPdf'])->name('quotes.pdf');
    Route::get('/Quote/{quote}/print', [QuoteController::class, 'printView'])->name('quotes.print');

    // Quote AJAX endpoints
    Route::post('/Quote/calculate-totals', [QuoteController::class, 'calculateTotals'])->name('quotes.calculate-totals');
    Route::get('/quote/next-number', [QuoteController::class, 'getNextQuoteNumber'])->name('quotes.next-number');

    // Purchase Orders
    Route::get('/po', [PurchaseOrderController::class, 'index'])->name('po.index');
    Route::get('/po/create', [PurchaseOrderController::class, 'create'])->name('po.create');
    Route::post('/po', [PurchaseOrderController::class, 'store'])->name('po.store');
    Route::get('/po/{purchaseOrder}', [PurchaseOrderController::class, 'show'])->name('po.show');
    Route::get('/po/{purchaseOrder}/edit', [PurchaseOrderController::class, 'edit'])->name('po.edit');
    Route::put('/po/{purchaseOrder}', [PurchaseOrderController::class, 'update'])->name('po.update');
    Route::delete('/po/{purchaseOrder}', [PurchaseOrderController::class, 'destroy'])->name('po.destroy');
    Route::get('/po/{purchaseOrder}/pdf', [PurchaseOrderController::class, 'downloadPdf'])->name('po.pdf');

    // ARO
    Route::get('/aro', [AroController::class, 'index'])->name('aro.index');
    Route::get('/aro/create', [AroController::class, 'create'])->name('aro.create');
    Route::post('/aro', [AroController::class, 'store'])->name('aro.store');
    Route::get('/aro/{aro}', [AroController::class, 'show'])->name('aro.show');
    Route::get('/aro/{aro}/edit', [AroController::class, 'edit'])->name('aro.edit');
    Route::put('/aro/{aro}', [AroController::class, 'update'])->name('aro.update');
    Route::delete('/aro/{aro}', [AroController::class, 'destroy'])->name('aro.destroy');
    Route::get('/aro/{aro}/pdf', [AroController::class, 'generatePDF'])->name('aro.pdf');
    Route::get('/aro/{aro}/print', [AroController::class, 'printView'])->name('aro.print');

    // Delivery Notes
    Route::get('/delivery-notes', [DeliveryNoteController::class, 'index'])->name('delivery-notes.index');
    Route::get('/delivery-notes/create', [DeliveryNoteController::class, 'create'])->name('delivery-notes.create');
    Route::post('/delivery-notes', [DeliveryNoteController::class, 'store'])->name('delivery-notes.store');
    Route::get('/delivery-notes/{deliveryNote}', [DeliveryNoteController::class, 'show'])->name('delivery-notes.show');
    Route::get('/delivery-notes/{deliveryNote}/edit', [DeliveryNoteController::class, 'edit'])->name('delivery-notes.edit');
    Route::put('/delivery-notes/{deliveryNote}', [DeliveryNoteController::class, 'update'])->name('delivery-notes.update');
    Route::delete('/delivery-notes/{deliveryNote}', [DeliveryNoteController::class, 'destroy'])->name('delivery-notes.destroy');
    Route::get('/delivery-notes/{deliveryNote}/pdf', [DeliveryNoteController::class, 'downloadPdf'])->name('delivery-notes.pdf');
    Route::get('/delivery-notes/{deliveryNote}/print', [DeliveryNoteController::class, 'printView'])->name('delivery-notes.print');

    // Invoices
    Route::get('/invoices', [InvoiceController::class, 'index'])->name('invoices.index');
    Route::get('/invoices/create', [InvoiceController::class, 'create'])->name('invoices.create');
    Route::post('/invoices', [InvoiceController::class, 'store'])->name('invoices.store');
    Route::get('/invoices/{invoice}', [InvoiceController::class, 'show'])->name('invoices.show');
    Route::get('/invoices/{invoice}/edit', [InvoiceController::class, 'edit'])->name('invoices.edit');
    Route::put('/invoices/{invoice}', [InvoiceController::class, 'update'])->name('invoices.update');
    Route::delete('/invoices/{invoice}', [InvoiceController::class, 'destroy'])->name('invoices.destroy');
    Route::get('/invoices/{invoice}/pdf', [InvoiceController::class, 'downloadPdf'])->name('invoices.pdf');
    Route::get('/invoices/{invoice}/print', [InvoiceController::class, 'printView'])->name('invoices.print');

    // Document
    Route::get('/document', [DocumentController::class, 'index'])->name('documents.index');
    Route::get('/document/{document}', [DocumentController::class, 'show'])->name('documents.show');
    Route::get('/document/{document}/download', [DocumentController::class, 'download'])->name('documents.download');

    // Quote Products
    Route::get('/quote-products', [QuoteProductController::class, 'index']);
    Route::get('/quote-products/create', [QuoteProductController::class, 'create']);
    Route::post('/quote-products', [QuoteProductController::class, 'store']);
    Route::get('/quote-products/{quoteProduct}', [QuoteProductController::class, 'show']);
    Route::get('/quote-products/{quoteProduct}/edit', [QuoteProductController::class, 'edit']);
    Route::put('/quote-products/{quoteProduct}', [QuoteProductController::class, 'update']);
    Route::delete('/quote-products/{quoteProduct}', [QuoteProductController::class, 'destroy']);

    // Invoice Products
    Route::get('/invoice-products', [InvoiceProductController::class, 'index']);
    Route::get('/invoice-products/create', [InvoiceProductController::class, 'create']);
    Route::post('/invoice-products', [InvoiceProductController::class, 'store']);
    Route::get('/invoice-products/{invoiceProduct}', [InvoiceProductController::class, 'show']);
    Route::get('/invoice-products/{invoiceProduct}/edit', [InvoiceProductController::class, 'edit']);
    Route::put('/invoice-products/{invoiceProduct}', [InvoiceProductController::class, 'update']);
    Route::delete('/invoice-products/{invoiceProduct}', [InvoiceProductController::class, 'destroy']);

   });
