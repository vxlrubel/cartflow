<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Address;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AddressController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $addresses = Address::where('user_id', $request->user()->id)->get();

        return response()->json($addresses);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'address' => 'required|string',
            'city' => 'required|string',
            'country' => 'required|string',
        ]);

        $address = Address::create([
            'user_id' => $request->user()->id,
            ...$request->only(['address', 'city', 'country']),
        ]);

        return response()->json($address, 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $address = Address::where('user_id', $request->user()->id)->findOrFail($id);
        $address->update($request->all());

        return response()->json($address);
    }

    public function destroy(Request $request, int $id): JsonResponse
    {
        $address = Address::where('user_id', $request->user()->id)->findOrFail($id);
        $address->delete();

        return response()->json(['message' => 'Address deleted']);
    }
}
