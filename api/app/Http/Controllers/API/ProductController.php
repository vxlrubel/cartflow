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
        $query = Product::with(['categories', 'category', 'brand', 'images']);

        if ($request->category || $request->category_id) {
            $query->where('category_id', $request->category ?? $request->category_id);
        }
        if ($request->brand) {
            $query->where('brand_id', $request->brand);
        }
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('description', 'like', '%'.$request->search.'%');
            });
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }

        if ($request->trashed) {
            $query->onlyTrashed();
        }

        $sortBy = $request->sort_by ?? 'name';
        $sortOrder = $request->sort_order ?? 'asc';
        
        $allowedSortFields = ['name', 'status', 'price', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        }

        $products = $query->paginate($request->per_page ?? 15);

        return response()->json($products);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'slug' => 'nullable|string|regex:/^[a-z0-9-]+$/|unique:products,slug',
            'price' => 'required|numeric|min:0',
            'sale_price' => 'nullable|numeric|min:0',
            'category_id' => 'nullable|exists:categories,id',
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'brand_id' => 'nullable|exists:brands,id',
            'stock' => 'required|integer|min:0',
            'sku' => 'required|string|unique:products',
            'status' => 'sometimes|in:active,inactive',
            'images' => 'nullable|array',
        ]);

        $slug = $request->slug ? $request->slug : Str::slug($request->name);
        
        $product = Product::create([
            ...$request->except('images', 'category_ids'),
            'slug' => $slug,
        ]);

        if ($request->category_ids) {
            $product->categories()->sync($request->category_ids);
        }

        if ($request->images) {
            foreach ($request->images as $url) {
                $product->images()->create(['url' => $url]);
            }
        }

        return response()->json($product->load(['images', 'categories']), 201);
    }

    public function show($id): JsonResponse
    {
        $product = Product::with(['categories', 'category', 'brand', 'images', 'variations.attributeValues'])->findOrFail((int) $id);

        return response()->json($product);
    }

    public function update(Request $request, $id): JsonResponse
    {
        $product = Product::findOrFail((int) $id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'slug' => 'sometimes|string|regex:/^[a-z0-9-]+$/|unique:products,slug,'.$id,
            'price' => 'sometimes|numeric|min:0',
            'sku' => 'sometimes|unique:products,sku,'.$id,
            'category_ids' => 'nullable|array',
            'category_ids.*' => 'exists:categories,id',
            'images' => 'nullable|array',
        ]);

        if ($request->slug) {
            $product->update(['slug' => $request->slug]);
        }
        
        $product->update($request->except('images', 'category_ids', 'slug'));

        if ($request->has('category_ids')) {
            $product->categories()->sync($request->category_ids ?? []);
        }

        if ($request->has('images')) {
            $product->images()->delete();
            foreach ($request->images as $url) {
                $product->images()->create(['url' => $url]);
            }
        }

        return response()->json($product->load(['images', 'categories']));
    }

    public function destroy($id): JsonResponse
    {
        $product = Product::findOrFail((int) $id);
        $product->delete();

        return response()->json(['message' => 'Product deleted']);
    }

    public function restore($id): JsonResponse
    {
        $product = Product::onlyTrashed()->findOrFail((int) $id);
        $product->restore();

        return response()->json($product);
    }

    public function forceDelete($id): JsonResponse
    {
        $product = Product::onlyTrashed()->findOrFail((int) $id);
        $product->forceDelete();

        return response()->json(['message' => 'Product permanently deleted']);
    }

    public function trash(): JsonResponse
    {
        $products = Product::onlyTrashed()->with(['categories', 'category', 'brand'])->paginate();

        return response()->json($products);
    }

    public function bulkSoftDelete(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array']);
        
        Product::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => 'Products moved to trash']);
    }

    public function bulkActive(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array']);
        
        Product::whereIn('id', $request->ids)->update(['status' => 'active']);

        return response()->json(['message' => 'Products activated']);
    }

    public function bulkInactive(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array']);
        
        Product::whereIn('id', $request->ids)->update(['status' => 'inactive']);

        return response()->json(['message' => 'Products deactivated']);
    }
}
