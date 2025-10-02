<?php

use App\Http\Controllers\DeliveryNoteController;
use App\Http\Controllers\InvoiceController;
use Illuminate\Support\Facades\Route;

// API routes for AJAX requests
Route::get('/aros/{aro}/products', [DeliveryNoteController::class, 'getAroProducts']);
Route::get('/delivery-notes/{deliveryNote}/products', [InvoiceController::class, 'getDeliveryNoteProducts']);
Route::get('/quotes/{quote}/products', [InvoiceController::class, 'getQuoteProducts']);

// use Illuminate\Support\Facades\Route;
// use App\Http\Controllers\AuthController;

// Route::get("/login", [AuthController::class, "Login"])->name('login');
// Route::post("/login", [AuthController::class, "storeLogin"])->name('store-login');
