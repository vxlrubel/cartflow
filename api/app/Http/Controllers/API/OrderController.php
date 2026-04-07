<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OrderController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Order::with(['user', 'items.product']);

        if ($request->status) {
            $query->where('status', $request->status);
        }
        if ($request->user_id) {
            $query->where('user_id', $request->user_id);
        }

        $orders = $query->paginate($request->per_page ?? 15);

        return response()->json($orders);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'items' => 'required|array|min:1',
            'items.*.product_id' => 'required|exists:products,id',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        $totalAmount = collect($request->items)->sum(fn ($item) => $item['price'] * $item['quantity']);

        $order = Order::create([
            'user_id' => $request->user_id,
            'total_amount' => $totalAmount,
            'status' => 'pending',
            'payment_status' => 'pending',
        ]);

        foreach ($request->items as $item) {
            $order->items()->create($item);
        }

        return response()->json($order->load(['user', 'items.product']), 201);
    }

    public function show(int $id): JsonResponse
    {
        $order = Order::with(['user', 'items.product', 'payment'])->findOrFail($id);

        return response()->json($order);
    }

    public function updateStatus(Request $request, int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $request->validate(['status' => 'required|in:pending,paid,shipped,delivered,cancelled']);
        $order->update(['status' => $request->status]);

        return response()->json($order);
    }

    public function destroy(int $id): JsonResponse
    {
        $order = Order::findOrFail($id);
        $order->delete();

        return response()->json(['message' => 'Order deleted']);
    }

    public function trash(): JsonResponse
    {
        $orders = Order::onlyTrashed()->with(['user'])->paginate();

        return response()->json($orders);
    }

    public function restore(int $id): JsonResponse
    {
        $order = Order::onlyTrashed()->findOrFail($id);
        $order->restore();

        return response()->json($order);
    }
}
