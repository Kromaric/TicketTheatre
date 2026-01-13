<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Services\PaymentService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Stripe\Exception\ApiErrorException;

class PaymentController extends Controller
{
    protected PaymentService $paymentService;

    public function __construct(PaymentService $paymentService)
    {
        $this->paymentService = $paymentService;
    }

    /**
     * List all payments.
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $query = \App\Models\Payment::query();

            // Filtrer par statut si fourni
            if ($request->has('status')) {
                $query->where('status', $request->status);
            }

            // Filtrer par utilisateur si fourni
            if ($request->has('user_id')) {
                $query->where('user_id', $request->user_id);
            }

            // Pagination
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

    /**
     * Create a new payment intent.
     *
     * @param Request $request
     * @return JsonResponse
     */
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
            $payment = $this->paymentService->createPaymentIntent($request->all());

            return response()->json([
                'success' => true,
                'data' => $payment,
                'client_secret' => $payment->stripe_payment_intent_id,
            ], 201);
        } catch (ApiErrorException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Failed to create payment intent',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Confirm a payment.
     *
     * @param Request $request
     * @param string $paymentIntentId
     * @return JsonResponse
     */
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

    /**
     * Get payment details.
     *
     * @param int $paymentId
     * @return JsonResponse
     */
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

    /**
     * Get user payments.
     *
     * @param int $userId
     * @return JsonResponse
     */
    public function getUserPayments(int $userId): JsonResponse
    {
        $payments = $this->paymentService->getUserPayments($userId);

        return response()->json([
            'success' => true,
            'data' => $payments,
        ]);
    }

    /**
     * Cancel a payment.
     *
     * @param string $paymentIntentId
     * @return JsonResponse
     */
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

    /**
     * Refund a payment.
     *
     * @param Request $request
     * @param int $paymentId
     * @return JsonResponse
     */
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
