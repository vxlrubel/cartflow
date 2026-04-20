<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Product;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InventoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'brand']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%'.$request->search.'%')
                  ->orWhere('sku', 'like', '%'.$request->search.'%');
            });
        }

        $sortBy = $request->sort_by ?? 'name';
        $sortOrder = $request->sort_order ?? 'asc';
        
        $allowedSortFields = ['name', 'sku', 'stock', 'price', 'created_at', 'updated_at'];
        if (in_array($sortBy, $allowedSortFields)) {
            $query->orderBy($sortBy, $sortOrder === 'desc' ? 'desc' : 'asc');
        }

        $products = $query->select(
            'id', 'name', 'slug', 'sku', 'stock', 'price', 
            'category_id', 'brand_id', 'status', 'created_at', 'updated_at'
        )->paginate($request->per_page ?? 15);

        $products->getCollection()->transform(function ($product) {
            $product->product_name = $product->name;
            return $product;
        });

        return response()->json($products);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'stock' => 'sometimes|integer|min:0',
            'sku' => 'sometimes|string|unique:products,sku,'.$id,
            'low_stock_threshold' => 'sometimes|integer|min:0',
        ]);

        $product = Product::findOrFail($id);

        if ($request->has('stock')) {
            $product->stock = $request->stock;
        }
        if ($request->has('sku')) {
            $product->sku = $request->sku;
        }
        if ($request->has('low_stock_threshold')) {
            $product->low_stock_threshold = $request->low_stock_threshold;
        }
        
        $product->save();

        return response()->json($product);
    }

    public function bulkUpdate(Request $request): JsonResponse
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'required|exists:products,id',
            'items.*.stock' => 'required|integer|min:0',
        ]);

        $updated = [];
        foreach ($request->items as $item) {
            $product = Product::find($item['id']);
            if ($product) {
                $product->stock = $item['stock'];
                $product->save();
                $updated[] = $product->id;
            }
        }

        return response()->json([
            'message' => 'Stock updated successfully',
            'updated' => $updated,
        ]);
    }

    public function alerts(Request $request): JsonResponse
    {
        $threshold = $request->threshold ?? 10;
        
        $alerts = Product::where(function ($query) use ($threshold) {
            $query->where('stock', '<=', $threshold)
                  ->orWhere(function ($q) {
                      $q->where('stock', '=', 0);
                  });
        })
        ->with(['category', 'brand'])
        ->select(
            'id', 'name', 'sku', 'stock', 'price', 
            'category_id', 'brand_id', 'low_stock_threshold',
            'created_at', 'updated_at'
        )
        ->get()
        ->map(function ($product) use ($threshold) {
            $alertThreshold = $product->low_stock_threshold ?? $threshold;
            return [
                'id' => $product->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'name' => $product->name,
                'sku' => $product->sku,
                'stock' => $product->stock,
                'threshold' => $alertThreshold,
                'price' => $product->price,
                'status' => $product->stock === 0 ? 'out_of_stock' : 'low_stock',
                'created_at' => $product->created_at,
            ];
        });

        return response()->json(['data' => $alerts]);
    }

    public function dismissAlert(int $id): JsonResponse
    {
        $product = Product::findOrFail($id);
        $product->alert_dismissed = true;
        $product->save();

        return response()->json(['message' => 'Alert dismissed']);
    }

    public function skus(Request $request): JsonResponse
    {
        $query = Product::with(['category', 'brand']);

        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->where('sku', 'like', '%'.$request->search.'%')
                  ->orWhere('name', 'like', '%'.$request->search.'%');
            });
        }

        $products = $query->select(
            'id', 'name', 'sku', 'stock', 'price', 
            'category_id', 'brand_id', 'status', 'created_at'
        )
        ->whereNotNull('sku')
        ->paginate($request->per_page ?? 15);

        $products->getCollection()->transform(function ($product) {
            return [
                'id' => $product->id,
                'product_id' => $product->id,
                'product_name' => $product->name,
                'name' => $product->name,
                'sku' => $product->sku,
                'stock' => $product->stock,
                'price' => $product->price,
                'status' => $product->status,
                'auto_generated' => $this->isAutoGenerated($product->sku),
                'created_at' => $product->created_at,
            ];
        });

        return response()->json($products);
    }

    public function updateSku(Request $request, int $id): JsonResponse
    {
        $request->validate([
            'sku' => 'required|string|unique:products,sku,'.$id,
        ]);

        $product = Product::findOrFail($id);
        $product->sku = $request->sku;
        $product->save();

        return response()->json($product);
    }

    public function generateSku(Request $request): JsonResponse
    {
        $request->validate([
            'product_id' => 'nullable|exists:products,id',
            'prefix' => 'required|string|max:20',
        ]);

        $prefix = strtoupper($request->prefix);
        
        // Find the highest existing SKU with this prefix
        $latestSku = Product::where('sku', 'like', $prefix.'-%')
            ->orderBy('sku', 'desc')
            ->first();

        if ($latestSku) {
            $parts = explode('-', $latestSku->sku);
            $number = end($parts) + 1;
        } else {
            $number = 1;
        }

        $sku = $prefix.'-'.str_pad($number, 4, '0', STR_PAD_LEFT);

        if ($request->product_id) {
            $product = Product::findOrFail($request->product_id);
            $product->sku = $sku;
            $product->save();
            return response()->json($product);
        }

        return response()->json(['sku' => $sku]);
    }

    private function isAutoGenerated(string $sku): bool
    {
        return (bool) preg_match('/^[A-Z]+-\d{4}$/', $sku);
    }
}