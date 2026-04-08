<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Category::with('parent')->withCount('products');
        if ($request->parent_id === 'null') {
            $query->whereNull('parent_id');
        }
        $categories = $query->paginate($request->per_page ?? 15);

        return response()->json($categories);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|max:255']);
        $category = Category::create($request->all());

        return response()->json($category, 201);
    }

    public function show(int $id): JsonResponse
    {
        $category = Category::with(['parent', 'children', 'products'])->findOrFail($id);

        return response()->json($category);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $request->validate(['name' => 'sometimes|string|max:255']);
        $category->update($request->all());

        return response()->json($category);
    }

    public function destroy(int $id): JsonResponse
    {
        $category = Category::findOrFail($id);
        $category->delete();

        return response()->json(['message' => 'Category deleted']);
    }

    public function restore(int $id): JsonResponse
    {
        $category = Category::onlyTrashed()->findOrFail($id);
        $category->restore();

        return response()->json($category);
    }
}
