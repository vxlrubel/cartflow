<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\EmailCampaign;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class EmailCampaignController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $campaigns = EmailCampaign::when($request->status, fn($q) => $q->where('status', $request->status))
            ->paginate($request->per_page ?? 15);

        return response()->json($campaigns);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'subject' => 'required|string|max:255',
            'content' => 'required',
            'scheduled_at' => 'nullable|date',
        ]);

        $campaign = EmailCampaign::create([
            ...$request->except('status'),
            'created_by' => auth()->id(),
            'status' => $request->scheduled_at ? 'scheduled' : 'draft',
        ]);

        return response()->json($campaign, 201);
    }

    public function show(int $id): JsonResponse
    {
        $campaign = EmailCampaign::findOrFail($id);

        return response()->json($campaign);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $campaign = EmailCampaign::findOrFail($id);
        
        if ($campaign->status === 'sent') {
            return response()->json(['error' => 'Cannot update sent campaign'], 400);
        }

        $campaign->update($request->except('status'));

        if ($request->has('scheduled_at') && $request->scheduled_at) {
            $campaign->update(['status' => 'scheduled']);
        }

        return response()->json($campaign);
    }

    public function destroy(int $id): JsonResponse
    {
        $campaign = EmailCampaign::findOrFail($id);
        $campaign->delete();

        return response()->json(['message' => 'Campaign deleted']);
    }

    public function send(int $id): JsonResponse
    {
        $campaign = EmailCampaign::findOrFail($id);

        if ($campaign->status === 'sent') {
            return response()->json(['error' => 'Campaign already sent'], 400);
        }

        $campaign->update([
            'status' => 'sent',
            'sent_at' => now(),
        ]);

        return response()->json(['message' => 'Campaign sent successfully']);
    }

    public function restore(int $id): JsonResponse
    {
        $campaign = EmailCampaign::withTrashed()->findOrFail($id);
        $campaign->restore();

        return response()->json($campaign);
    }

    public function forceDelete(int $id): JsonResponse
    {
        $campaign = EmailCampaign::withTrashed()->findOrFail($id);
        $campaign->forceDelete();

        return response()->json(['message' => 'Campaign permanently deleted']);
    }

    public function trash(Request $request): JsonResponse
    {
        $campaigns = EmailCampaign::onlyTrashed()
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->paginate($request->per_page ?? 15);

        return response()->json($campaigns);
    }
}