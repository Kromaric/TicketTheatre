<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\WebhookController;

Route::get('/health', function () {
    return response()->json(['status' => 'ok', 'service' => 'payment-service']);
});

Route::prefix('payments')->group(function () {
    Route::get('/', [PaymentController::class, 'index']);
    Route::post('/', [PaymentController::class, 'createPaymentIntent']);
    Route::post('/checkout', [PaymentController::class, 'createCheckoutSession']);
    Route::get('/{paymentId}', [PaymentController::class, 'show']);
    Route::get('/user/{userId}', [PaymentController::class, 'getUserPayments']);
    Route::post('/{paymentIntentId}/confirm', [PaymentController::class, 'confirmPayment']);
    Route::post('/{paymentIntentId}/cancel', [PaymentController::class, 'cancelPayment']);
    Route::post('/{paymentId}/refund', [PaymentController::class, 'refund']);
});

Route::post('/webhooks/stripe', [WebhookController::class, 'handleWebhook'])
    ->withoutMiddleware([\Illuminate\Foundation\Http\Middleware\ValidateCsrfToken::class]);
