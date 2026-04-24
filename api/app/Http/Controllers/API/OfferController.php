<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Offer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OfferController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Offer::query();
        if ($request->type) {
            $query->where('type', $request->type);
        }
        if ($request->status) {
            $query->where('status', $request->status);
        }
        $offers = $query->with('rules')->paginate($request->per_page ?? 15);

        return response()->json($offers);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:bxgy,flash,percentage,black_friday',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after:start_date',
            'status' => 'sometimes|in:active,inactive',
            'rules' => 'nullable|array',
            'rules.*.rule_type' => 'required|string',
            'rules.*.conditions' => 'required|array',
        ]);

        $offer = Offer::create($request->except('rules'));

        if ($request->rules) {
            foreach ($request->rules as $rule) {
                $offer->rules()->create($rule);
            }
        }

        return response()->json($offer->load('rules'), 201);
    }

    public function show(int $id): JsonResponse
    {
        $offer = Offer::with('rules')->findOrFail($id);

        return response()->json($offer);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $offer = Offer::findOrFail($id);
        $offer->update($request->except('rules'));

        if (isset($request->rules)) {
            $offer->rules()->delete();
            foreach ($request->rules as $rule) {
                $offer->rules()->create($rule);
            }
        }

        return response()->json($offer->fresh('rules'));
    }

    public function destroy(int $id): JsonResponse
    {
        $offer = Offer::findOrFail($id);
        $offer->delete();

        return response()->json(['message' => 'Offer deleted']);
    }

    public function restore(int $id): JsonResponse
    {
        $offer = Offer::withTrashed()->findOrFail($id);
        $offer->restore();

        return response()->json($offer);
    }

    public function forceDelete(int $id): JsonResponse
    {
        $offer = Offer::withTrashed()->findOrFail($id);
        $offer->forceDelete();

        return response()->json(['message' => 'Offer permanently deleted']);
    }

    public function trash(Request $request): JsonResponse
    {
        $offers = Offer::onlyTrashed()
            ->when($request->type, fn($q) => $q->where('type', $request->type))
            ->with('rules')
            ->paginate($request->per_page ?? 15);

        return response()->json($offers);
    }
}
