<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Permission;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PermissionController extends Controller
{
    public function index(): JsonResponse
    {
        $permissions = Permission::all();

        return response()->json($permissions);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate(['name' => 'required|string|unique:permissions']);
        $permission = Permission::create(['name' => $request->name]);

        return response()->json($permission, 201);
    }
}
