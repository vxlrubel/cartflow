<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PaymentController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $payments = Payment::with('order')->paginate($request->per_page ?? 15);

        return response()->json($payments);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'method' => 'required|string',
            'transaction_id' => 'nullable|string',
            'status' => 'sometimes|in:pending,success,failed',
        ]);

        $payment = Payment::create($request->all());

        if ($request->status === 'success') {
            Order::where('id', $request->order_id)->update([
                'payment_status' => 'paid',
                'status' => 'paid',
            ]);
        }

        return response()->json($payment->load('order'), 201);
    }

    public function show(int $id): JsonResponse
    {
        $payment = Payment::with('order')->findOrFail($id);

        return response()->json($payment);
    }
}
