<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\CustomerGroup;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CustomerGroupController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->per_page ?? 15), 50);
        $search = $request->search;

        $query = CustomerGroup::withCount('customers');

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('description', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->boolean('is_active')) {
            $query->where('is_active', true);
        }

        $groups = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($groups);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'nullable|in:segment,newsletter,vip,inactive',
            'color' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $group = CustomerGroup::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name) . '-' . Str::random(5),
            'description' => $request->description,
            'type' => $request->type ?? 'segment',
            'color' => $request->color ?? '#3B82F6',
            'is_active' => $request->boolean('is_active', true),
        ]);

        return response()->json($group, 201);
    }

    public function show(int $id): JsonResponse
    {
        $group = CustomerGroup::with(['customers:id,name,email,phone'])->findOrFail($id);

        return response()->json($group);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $group = CustomerGroup::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'nullable|string|max:1000',
            'type' => 'nullable|in:segment,newsletter,vip,inactive',
            'color' => 'nullable|string|max:20',
            'is_active' => 'nullable|boolean',
        ]);

        $group->update($request->only(['name', 'description', 'type', 'color', 'is_active']));

        return response()->json($group);
    }

    public function destroy(int $id): JsonResponse
    {
        $group = CustomerGroup::findOrFail($id);
        $group->delete();

        return response()->json(['message' => 'Customer group deleted']);
    }

    public function addCustomers(Request $request, int $id): JsonResponse
    {
        $group = CustomerGroup::findOrFail($id);

        $request->validate([
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'exists:users,id',
        ]);

        $group->customers()->syncWithoutDetaching($request->customer_ids);

        return response()->json(['message' => 'Customers added to group']);
    }

    public function removeCustomers(Request $request, int $id): JsonResponse
    {
        $group = CustomerGroup::findOrFail($id);

        $request->validate([
            'customer_ids' => 'required|array|min:1',
            'customer_ids.*' => 'exists:users,id',
        ]);

        $group->customers()->detach($request->customer_ids);

        return response()->json(['message' => 'Customers removed from group']);
    }
}