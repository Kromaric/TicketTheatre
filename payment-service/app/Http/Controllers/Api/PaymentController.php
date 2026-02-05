<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    public function index(Request $request): JsonResponse
    {
        try {
            $query = \App\Models\Payment::query();

            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            $perPage = $request->get('per_page', 15);
            $payments = $query->orderBy('created_at', 'desc')->paginate($perPage);

            return response()->json([
                'success' => true,
                'data' => $payments->items(),
                'meta' => [
                    'current_page' => $payments->currentPage(),
                    'last_page' => $payments->lastPage(),
                    'per_page' => $payments->perPage(),
                    'total' => $payments->total(),
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to fetch payments',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createCheckoutSession(Request $request): JsonResponse
    {
        Log::info('Create checkout session request', $request->all());

        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'sometimes|string|size:3',
            'user_id' => 'required|integer',
            'order_id' => 'sometimes|integer',
            'customer_email' => 'sometimes|email',
            'description' => 'sometimes|string|max:500',
            'success_url' => 'required|url',
            'cancel_url' => 'required|url',
            'metadata' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            Log::error('Validation failed', $validator->errors()->toArray());
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->paymentService->createCheckoutSession($request->all());

            Log::info('Checkout session created', ['result' => $result]);

            return response()->json([
                'success' => true,
                'data' => $result['payment'],
                'session_id' => $result['session_id'],
                'checkout_url' => $result['checkout_url'],
            ], 201);
        } catch (ApiErrorException $e) {
            Log::error('Stripe API error', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create checkout session',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            Log::error('General error creating checkout', [
                'message' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            
            return response()->json([
                'success' => false,
                'message' => 'Failed to create checkout session',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function createPaymentIntent(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'required|numeric|min:0.01',
            'currency' => 'sometimes|string|size:3',
            'user_id' => 'required|integer',
            'order_id' => 'sometimes|integer',
            'customer_email' => 'sometimes|email',
            'description' => 'sometimes|string|max:500',
            'metadata' => 'sometimes|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->paymentService->createPaymentIntent($request->all());

            return response()->json([
                'success' => true,
                'data' => $result['payment'],
                'client_secret' => $result['client_secret'],
            ], 201);
        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function confirmPayment(Request $request, string $paymentIntentId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'payment_method_id' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $result = $this->paymentService->confirmPayment(
                $paymentIntentId,
                $request->payment_method_id
            );

            return response()->json([
                'success' => true,
                'data' => $result['payment'],
            ]);
        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to confirm payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function show(int $paymentId): JsonResponse
    {
        try {
            $payment = $this->paymentService->getPayment($paymentId);

            return response()->json([
                'success' => true,
                'data' => $payment,
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Payment not found',
            ], 404);
        }
    }

    public function getUserPayments(int $userId): JsonResponse
    {
        $payments = $this->paymentService->getUserPayments($userId);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    public function cancelPayment(string $paymentIntentId): JsonResponse
    {
        try {
            $payment = $this->paymentService->cancelPayment($paymentIntentId);

            return response()->json([
                'success' => true,
                'data' => $payment,
                'message' => 'Payment canceled successfully',
            ]);
        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to cancel payment',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    public function refund(Request $request, int $paymentId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'amount' => 'sometimes|numeric|min:0.01',
            'reason' => 'sometimes|string|in:duplicate,fraudulent,requested_by_customer',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        try {
            $payment = $this->paymentService->getPayment($paymentId);

            if ($payment->status !== 'succeeded') {
                return response()->json([
                    'success' => false,
                    'message' => 'Only succeeded payments can be refunded',
                ], 400);
            }

            $result = $this->paymentService->refundPayment(
                $payment,
                $request->amount ?? null,
                $request->reason ?? null
            );

            return response()->json([
                'success' => true,
                'data' => $result['payment'],
                'transaction' => $result['transaction'],
                'message' => 'Payment refunded successfully',
            ]);
        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to refund payment',
                'error' => $e->getMessage(),
            ], 500);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
