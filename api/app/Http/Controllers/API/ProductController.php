<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'brand', 'images']);

        if ($request->category || $request->category_id) {
            $query->where('category_id', $request->category ?? $request->category_id);
        }
        if ($request->brand) {
            $query->where('brand_id', $request->brand);
        }
        if ($request->search) {
            $query->where('name', 'like', '%'.$request->search.'%');
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        $sort = $request->sort ?? 'name_asc';
        $sortMap = [
            'name_asc' => ['name', 'asc'],
            'name_desc' => ['name', 'desc'],
            'price_asc' => ['price', 'asc'],
            'price_desc' => ['price', 'desc'],
        ];
        [$sortField, $sortDir] = $sortMap[$sort] ?? ['name', 'asc'];
        $query->orderBy($sortField, $sortDir);

        $products = $query->paginate($request->per_page ?? 15);

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'stock' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products',
            'status' => 'sometimes|in:active,inactive',
            'images' => 'nullable|array',
        ]);

        $product = Product::create([
            ...$request->except('images'),
            'slug' => Str::slug($request->name),
        ]);

        if ($request->images) {
            foreach ($request->images as $url) {
                $product->images()->create(['url' => $url]);
            }
        }

        return response()->json($product->load('images'), 201);
    }

    public function show(int $id): JsonResponse
    {
        $product = Product::with(['category', 'brand', 'images', 'variations.attributeValues'])->findOrFail($id);

        return response()->json($product);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $product = Product::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'price' => 'sometimes|numeric|min:0',
            'sku' => 'sometimes|unique:products,sku,'.$id,
        ]);

        $product->update($request->except('images'));

        if ($request->name) {
            $product->update(['slug' => Str::slug($request->name)]);
        }

        return response()->json($product->load('images'));
    }

    public function destroy(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }

    public function restore(int $id): JsonResponse
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->restore();

        return response()->json($product);
    }

    public function forceDelete(int $id): JsonResponse
    {
        $product = Product::onlyTrashed()->findOrFail($id);
        $product->forceDelete();

        return response()->json(['message' => 'Product permanently deleted']);
    }

    public function trash(): JsonResponse
    {
        $products = Product::onlyTrashed()->with(['category', 'brand'])->paginate();

        return response()->json($products);
    }
}
