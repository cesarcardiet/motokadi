<?php

use App\Http\Controllers\Api\AdminPaymentController;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CatalogController;
use App\Http\Controllers\Api\ClientApplicationController;
use App\Http\Controllers\Api\ClientPaymentController;
use App\Http\Controllers\Api\StaffApplicationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function () {
    Route::get('/health', fn () => response()->json([
        'ok' => true,
        'service' => 'motokadi-api',
        'version' => 'v1',
    ]));

    Route::get('/catalog/motorcycles', [CatalogController::class, 'motorcycles']);
    Route::get('/catalog/motorcycles/{motorcycle}', [CatalogController::class, 'motorcycle']);
    Route::get('/catalog/branches', [CatalogController::class, 'branches']);

    Route::post('/auth/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/auth/me', [AuthController::class, 'me']);
        Route::post('/auth/logout', [AuthController::class, 'logout']);

        // Cliente
        Route::get('/applications', [ClientApplicationController::class, 'index']);
        Route::post('/applications', [ClientApplicationController::class, 'store']);
        Route::get('/applications/{application}', [ClientApplicationController::class, 'show']);
        Route::post('/applications/{application}/documents/{document}', [ClientApplicationController::class, 'replaceDocument']);

        Route::get('/contracts', [ClientPaymentController::class, 'contracts']);
        Route::get('/vouchers', [ClientPaymentController::class, 'vouchers']);
        Route::get('/vouchers/{voucher}/pdf', [ClientPaymentController::class, 'downloadPdf']);
        Route::post('/installments/{installment}/vouchers', [ClientPaymentController::class, 'store']);

        // Staff / admin
        Route::get('/staff/applications', [StaffApplicationController::class, 'index']);
        Route::get('/staff/applications/{application}', [StaffApplicationController::class, 'show']);
        Route::post('/staff/applications/{application}/documents/{document}', [StaffApplicationController::class, 'reviewDocument']);
        Route::post('/staff/applications/{application}/location', [StaffApplicationController::class, 'verifyLocation']);
        Route::post('/staff/applications/{application}/approve', [StaffApplicationController::class, 'approve']);

        Route::get('/admin/payments', [AdminPaymentController::class, 'index']);
        Route::post('/admin/payments/{voucher}/approve', [AdminPaymentController::class, 'approve']);
        Route::post('/admin/payments/{voucher}/reject', [AdminPaymentController::class, 'reject']);
    });
});
