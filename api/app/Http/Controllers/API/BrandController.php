<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Brand;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BrandController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $brands = Brand::paginate($request->per_page ?? 15);

        return response()->json($brands);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255|unique:brands']);
        $brand = Brand::create(['name' => $request->name]);

        return response()->json($brand, 201);
    }

    public function show(int $id): JsonResponse
    {
        $brand = Brand::with('products')->findOrFail($id);

        return response()->json($brand);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);
        $request->validate(['name' => 'sometimes|string|max:255|unique:brands,name,'.$id]);
        $brand->update($request->all());

        return response()->json($brand);
    }

    public function destroy(int $id): JsonResponse
    {
        $brand = Brand::findOrFail($id);
        $brand->delete();

        return response()->json(['message' => 'Brand deleted']);
    }
}
