<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Wishlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $wishlists = Wishlist::where('user_id', $request->user()->id)->with('products')->get();

        return response()->json($wishlists);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['product_id' => 'required|exists:products,id']);

        $wishlist = Wishlist::firstOrCreate(['user_id' => $request->user()->id]);
        $wishlist->products()->syncWithoutDetaching([$request->product_id]);

        return response()->json(['message' => 'Added to wishlist'], 201);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $wishlist = Wishlist::where('user_id', $request->user()->id)->first();
        $wishlist?->products()->detach($id);

        return response()->json(['message' => 'Removed from wishlist']);
    }
}
