<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    public function index(): JsonResponse
    {
        $roles = Role::with('permissions')->get();

        return response()->json($roles);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|unique:roles']);
        $role = Role::create(['name' => $request->name]);

        return response()->json($role, 201);
    }

    public function show(int $id): JsonResponse
    {
        $role = Role::with('permissions')->findOrFail($id);

        return response()->json($role);
    }

    public function assignPermission(Request $request, int $id): JsonResponse
    {
        $role = Role::findOrFail($id);
        $request->validate(['permissions' => 'required|array', 'permissions.*' => 'exists:permissions,id']);
        $role->permissions()->sync($request->permissions);

        return response()->json($role->fresh('permissions'));
    }
}
