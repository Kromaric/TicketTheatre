<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WebhookController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

// Health check
Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'service' => 'payment-service']);
});

// Payment routes
Route::prefix('payments')->group(function () {
    // List all payments (for testing/admin)
    Route::get('/', [PaymentController::class, 'index']);
    
    // Create payment intent
    Route::post('/', [PaymentController::class, 'createPaymentIntent']);

    // Get payment by ID
    Route::get('/{paymentId}', [PaymentController::class, 'show']);

    // Get user payments
    Route::get('/user/{userId}', [PaymentController::class, 'getUserPayments']);

    // Confirm payment
    Route::post('/{paymentIntentId}/confirm', [PaymentController::class, 'confirmPayment']);

    // Cancel payment
    Route::post('/{paymentIntentId}/cancel', [PaymentController::class, 'cancelPayment']);

    // Refund payment
    Route::post('/{paymentId}/refund', [PaymentController::class, 'refund']);
});

// Stripe webhooks (no CSRF protection needed)
Route::post('/webhooks/stripe', [WebhookController::class, 'handleWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
