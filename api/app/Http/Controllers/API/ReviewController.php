<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Review;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ReviewController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Review::with(['product', 'user']);

        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->product_id) {
            $query->where('product_id', $request->product_id);
        }

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('comment', 'like', '%'.$request->search.'%');
            });
        }

        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';
        
        $allowedSortFields = ['rating', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        $reviews = $query->paginate($request->per_page ?? 15);

        return response()->json($reviews);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string',
        ]);

        $review = Review::create([
            'product_id' => $request->product_id,
            'user_id' => $request->user()->id ?? 1,
            'rating' => $request->rating,
            'comment' => $request->comment,
            'status' => 'pending',
        ]);

        return response()->json($review, 201);
    }

    public function show($id): JsonResponse
    {
        $review = Review::with(['product', 'user'])->findOrFail((int) $id);

        return response()->json($review);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $review = Review::findOrFail((int) $id);

        $request->validate([
            'rating' => 'sometimes|integer|min:1|max:5',
            'comment' => 'nullable|string',
            'status' => 'sometimes|in:pending,approved,rejected',
        ]);

        $review->update($request->all());

        return response()->json($review);
    }

    public function destroy($id): JsonResponse
    {
        $review = Review::findOrFail((int) $id);
        $review->delete();

        return response()->json(['message' => 'Review deleted']);
    }

    public function updateStatus(Request $request, $id): JsonResponse
    {
        $request->validate([
            'status' => 'required|in:pending,approved,rejected',
        ]);

        $review = Review::findOrFail((int) $id);
        $review->update(['status' => $request->status]);

        return response()->json($review);
    }
}