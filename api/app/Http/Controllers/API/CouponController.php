<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Coupon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CouponController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $coupons = Coupon::paginate($request->per_page ?? 15);

        return response()->json($coupons);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'code' => 'required|string|unique:coupons',
            'type' => 'required|in:product,category,cart',
            'discount_type' => 'required|in:fixed,percentage',
            'discount_value' => 'required|numeric|min:0',
            'max_usage' => 'nullable|integer|min:0',
            'expires_at' => 'nullable|date',
            'product_ids' => 'nullable|array',
            'category_ids' => 'nullable|array',
        ]);

        $coupon = Coupon::create($request->except(['product_ids', 'category_ids']));

        if ($request->product_ids) {
            $coupon->products()->sync($request->product_ids);
        }
        if ($request->category_ids) {
            $coupon->categories()->sync($request->category_ids);
        }

        return response()->json($coupon->load(['products', 'categories']), 201);
    }

    public function show(int $id): JsonResponse
    {
        $coupon = Coupon::with(['products', 'categories'])->findOrFail($id);

        return response()->json($coupon);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        $request->validate([
            'code' => 'sometimes|string|unique:coupons,code,'.$id,
        ]);
        $coupon->update($request->except(['product_ids', 'category_ids']));

        if (isset($request->product_ids)) {
            $coupon->products()->sync($request->product_ids);
        }
        if (isset($request->category_ids)) {
            $coupon->categories()->sync($request->category_ids);
        }

        return response()->json($coupon->fresh(['products', 'categories']));
    }

    public function destroy(int $id): JsonResponse
    {
        $coupon = Coupon::findOrFail($id);
        $coupon->delete();

        return response()->json(['message' => 'Coupon deleted']);
    }

    public function restore(int $id): JsonResponse
    {
        $coupon = Coupon::withTrashed()->findOrFail($id);
        $coupon->restore();

        return response()->json($coupon);
    }

    public function forceDelete(int $id): JsonResponse
    {
        $coupon = Coupon::withTrashed()->findOrFail($id);
        $coupon->forceDelete();

        return response()->json(['message' => 'Coupon permanently deleted']);
    }

    public function trash(Request $request): JsonResponse
    {
        $coupons = Coupon::onlyTrashed()
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->paginate($request->per_page ?? 15);

        return response()->json($coupons);
    }

    public function usage(Request $request): JsonResponse
    {
        $query = \App\Models\CouponUsage::with(['coupon', 'user'])
            ->when($request->coupon_id, fn($q) => $q->where('coupon_id', $request->coupon_id))
            ->when($request->user_id, fn($q) => $q->where('user_id', $request->user_id));

        $usages = $query->paginate($request->per_page ?? 15);

        return response()->json($usages);
    }

    public function apply(Request $request): JsonResponse
    {
        $request->validate(['code' => 'required|string']);

        $coupon = Coupon::where('code', $request->code)->first();

        if (! $coupon) {
            return response()->json(['error' => 'Invalid coupon code'], 404);
        }

        if ($coupon->expires_at && $coupon->expires_at->isPast()) {
            return response()->json(['error' => 'Coupon expired'], 400);
        }

        if ($coupon->max_usage > 0 && $coupon->used_count >= $coupon->max_usage) {
            return response()->json(['error' => 'Coupon usage limit reached'], 400);
        }

        $discount = 0;
        $total = $request->total ?? 0;

        if ($coupon->discount_type === 'fixed') {
            $discount = $coupon->discount_value;
        } else {
            $discount = ($total * $coupon->discount_value) / 100;
        }

        return response()->json([
            'coupon' => $coupon,
            'discount' => $discount,
            'final_total' => max(0, $total - $discount),
        ]);
    }
}
