<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Attribute;
use App\Models\ProductVariation;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AttributeController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $attributes = Attribute::with('values')->paginate($request->per_page ?? 15);

        return response()->json($attributes);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255']);
        $attribute = Attribute::create(['name' => $request->name]);

        return response()->json($attribute, 201);
    }

    public function addValue(Request $request, int $id): JsonResponse
    {
        $attribute = Attribute::findOrFail($id);
        $request->validate(['value' => 'required|string|max:255']);
        $value = $attribute->values()->create(['value' => $request->value]);

        return response()->json($value, 201);
    }

    public function storeVariation(Request $request, int $productId): JsonResponse
    {
        $request->validate([
            'sku' => 'required|string|unique:product_variations',
            'price' => 'required|numeric|min:0',
            'stock' => 'required|integer|min:0',
            'attribute_values' => 'required|array',
        ]);

        $variation = ProductVariation::create([
            'product_id' => $productId,
            'sku' => $request->sku,
            'price' => $request->price,
            'stock' => $request->stock,
        ]);

        $variation->attributeValues()->sync($request->attribute_values);

        return response()->json($variation->load('attributeValues'), 201);
    }

    public function updateVariation(Request $request, int $id): JsonResponse
    {
        $variation = ProductVariation::findOrFail($id);
        $request->validate([
            'sku' => 'sometimes|string|unique:product_variations,sku,'.$id,
            'price' => 'sometimes|numeric|min:0',
            'stock' => 'sometimes|integer|min:0',
        ]);
        $variation->update($request->all());

        return response()->json($variation->load('attributeValues'));
    }

    public function destroyVariation(int $id): JsonResponse
    {
        $variation = ProductVariation::findOrFail($id);
        $variation->delete();

        return response()->json(['message' => 'Variation deleted']);
    }
}
