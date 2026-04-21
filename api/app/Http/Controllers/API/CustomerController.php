<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class CustomerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->per_page ?? 15), 50);
        $search = $request->search;
        $sortBy = $request->sort_by ?? 'created_at';
        $sortOrder = $request->sort_order ?? 'desc';

        $customerRole = Role::where('name', 'customer')->first();

        $query = Customer::customSelect()
            ->with('role:id,name')
            ->where('role_id', $customerRole->id ?? 0);

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('phone', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $allowedSorts = ['name', 'email', 'created_at'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        if ($request->boolean('trashed')) {
            $query->onlyTrashed();
        }

        $customers = $query->paginate($perPage);

        return response()->json($customers);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'phone' => 'nullable|string|max:20',
        ]);

        $customerRole = Role::where('name', 'customer')->first();

        $customer = Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'role_id' => $customerRole->id,
        ]);

        return response()->json($customer, 201);
    }

    public function show(int $id): JsonResponse
    {
        $customer = Customer::with([
            'role:id,name',
            'addresses',
            'orders:id,user_id,total_amount,status,created_at',
        ])->findOrFail($id);

        return response()->json($customer);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $id,
            'phone' => 'sometimes|nullable|string|max:20',
            'status' => 'sometimes|in:active,inactive',
        ]);

        $customer->update($request->only(['name', 'email', 'phone', 'status']));

        return response()->json($customer);
    }

    public function destroy(int $id): JsonResponse
    {
        $customer = Customer::findOrFail($id);
        $customer->delete();

        return response()->json(['message' => 'Customer moved to trash']);
    }

    public function restore(int $id): JsonResponse
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->restore();

        return response()->json($customer);
    }

    public function forceDelete(int $id): JsonResponse
    {
        $customer = Customer::onlyTrashed()->findOrFail($id);
        $customer->forceDelete();

        return response()->json(['message' => 'Customer permanently deleted']);
    }

    public function bulkSoftDelete(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array|min:1']);
        Customer::whereIn('id', $request->ids)->delete();

        return response()->json(['message' => 'Customers moved to trash']);
    }

    public function bulkActive(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array|min:1']);
        Customer::whereIn('id', $request->ids)->update(['status' => 'active']);

        return response()->json(['message' => 'Customers activated']);
    }

    public function bulkInactive(Request $request): JsonResponse
    {
        $request->validate(['ids' => 'required|array|min:1']);
        Customer::whereIn('id', $request->ids)->update(['status' => 'inactive']);

        return response()->json(['message' => 'Customers deactivated']);
    }
}