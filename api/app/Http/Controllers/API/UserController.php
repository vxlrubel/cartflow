<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->per_page ?? 10), 50);

        $users = User::select('id', 'name', 'email', 'role_id', 'created_at')
            ->with('role:id,name')
            ->paginate($perPage);

        return response()->json($users);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users',
            'password' => 'required|min:8',
            'role_id' => 'nullable|exists:roles,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role_id' => $request->role_id,
        ]);

        return response()->json($user, 201);
    }

    public function show(int $id): JsonResponse
    {
        $user = User::with('role')->findOrFail($id);

        return response()->json($user);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,'.$id,
            'password' => 'sometimes|min:8',
            'role_id' => 'sometimes|exists:roles,id',
        ]);

        $user->update($request->only(['name', 'email', 'role_id']));

        if ($request->password) {
            $user->update(['password' => Hash::make($request->password)]);
        }

        return response()->json($user);
    }

    public function destroy(int $id): JsonResponse
    {
        $user = User::findOrFail($id);
        $user->delete();

        return response()->json(['message' => 'User deleted']);
    }

    public function restore(int $id): JsonResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->restore();

        return response()->json($user);
    }

    public function forceDelete(int $id): JsonResponse
    {
        $user = User::onlyTrashed()->findOrFail($id);
        $user->forceDelete();

        return response()->json(['message' => 'User permanently deleted']);
    }
}
