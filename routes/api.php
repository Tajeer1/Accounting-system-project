<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\DashboardApiController;
use App\Http\Controllers\Api\InvoiceApiController;
use App\Http\Controllers\Api\PurchaseApiController;
use App\Http\Controllers\Api\ResourceApiController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    Route::get('/dashboard', [DashboardApiController::class, 'index']);

    Route::get('/purchases', [PurchaseApiController::class, 'index']);
    Route::post('/purchases', [PurchaseApiController::class, 'store']);
    Route::get('/purchases/{purchase}', [PurchaseApiController::class, 'show']);
    Route::put('/purchases/{purchase}', [PurchaseApiController::class, 'update']);
    Route::delete('/purchases/{purchase}', [PurchaseApiController::class, 'destroy']);

    Route::get('/invoices', [InvoiceApiController::class, 'index']);
    Route::post('/invoices', [InvoiceApiController::class, 'store']);
    Route::get('/invoices/{invoice}', [InvoiceApiController::class, 'show']);
    Route::put('/invoices/{invoice}', [InvoiceApiController::class, 'update']);
    Route::post('/invoices/{invoice}/mark-paid', [InvoiceApiController::class, 'markPaid']);
    Route::delete('/invoices/{invoice}', [InvoiceApiController::class, 'destroy']);

    Route::get('/bank-accounts', [ResourceApiController::class, 'bankAccounts']);
    Route::get('/projects', [ResourceApiController::class, 'projects']);
    Route::get('/categories', [ResourceApiController::class, 'categories']);
    Route::get('/chart-of-accounts', [ResourceApiController::class, 'chartOfAccounts']);
});
