<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->per_page ?? 20), 50);
        $category = $request->category;
        $search = $request->search;

        $query = Setting::query();

        if ($category) {
            $query->byCategory($category);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('key', 'like', "%{$search}%")
                    ->orWhere('value', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('active_only')) {
            $query->active();
        }

        $query->orderBy('sort_order')->orderBy('key');

        $settings = $query->paginate($perPage);

        return response()->json($settings);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'key' => 'required|string|max:255|unique:settings',
            'value' => 'required',
            'type' => 'required|in:string,integer,boolean,json,array',
            'category' => 'required|string|max:255',
            'is_active' => 'boolean',
            'sort_order' => 'integer',
        ]);

        $setting = Setting::create([
            'key' => $request->key,
            'value' => $request->type === 'json' ? json_encode($request->value) : $request->value,
            'type' => $request->type ?? 'string',
            'category' => $request->category,
            'is_active' => $request->boolean('is_active', true),
            'sort_order' => $request->integer('sort_order', 0),
        ]);

        return response()->json($setting, 201);
    }

    public function show(int $id): JsonResponse
    {
        $setting = Setting::findOrFail($id);

        return response()->json($setting);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $setting = Setting::findOrFail($id);

        $request->validate([
            'value' => 'sometimes',
            'type' => 'sometimes|in:string,integer,boolean,json,array',
            'category' => 'sometimes|string|max:255',
            'is_active' => 'sometimes|boolean',
            'sort_order' => 'sometimes|integer',
        ]);

        $updateData = $request->only(['value', 'type', 'category', 'is_active', 'sort_order']);

        if (isset($updateData['value']) && ($request->type ?? $setting->type) === 'json') {
            $updateData['value'] = json_encode($updateData['value']);
        }

        $setting->update($updateData);

        return response()->json($setting);
    }

    public function destroy(int $id): JsonResponse
    {
        $setting = Setting::findOrFail($id);
        $setting->delete();

        return response()->json(['message' => 'Setting moved to trash']);
    }

    public function restore(int $id): JsonResponse
    {
        $setting = Setting::onlyTrashed()->findOrFail($id);
        $setting->restore();

        return response()->json($setting);
    }

    public function forceDelete(int $id): JsonResponse
    {
        $setting = Setting::onlyTrashed()->findOrFail($id);
        $setting->forceDelete();

        return response()->json(['message' => 'Setting permanently deleted']);
    }

    public function trash(): JsonResponse
    {
        $settings = Setting::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(15);

        return response()->json($settings);
    }

    public function byCategory(Request $request): JsonResponse
    {
        $category = $request->category ?? 'general';

        $settings = Setting::byCategory($category)->active()->orderBy('sort_order')->orderBy('key')->get();

        $grouped = $settings->groupBy('category')->map(fn ($items) => $items->mapWithKeys(fn ($s) => [$s->key => $s->value]));

        return response()->json([
            'category' => $category,
            'settings' => $grouped[$category] ?? [],
        ]);
    }

    public function updateMultiple(Request $request): JsonResponse
    {
        $request->validate([
            'settings' => 'required|array',
        ]);

        foreach ($request->settings as $key => $value) {
            Setting::updateOrCreate(
                ['key' => $key],
                ['value' => is_array($value) ? json_encode($value) : $value, 'type' => is_array($value) ? 'json' : 'string']
            );
        }

        return response()->json(['message' => 'Settings updated successfully']);
    }

    public function getConfig(): JsonResponse
    {
        $categories = Setting::selectRaw('category, COUNT(*) as count')
            ->groupBy('category')
            ->get()
            ->pluck('count', 'category');

        $allSettings = Setting::active()
            ->orderBy('category')
            ->orderBy('key')
            ->get()
            ->groupBy('category')
            ->map(fn ($items) => $items->mapWithKeys(fn ($s) => [$s->key => $s->value]));

        return response()->json([
            'categories' => $categories,
            'settings' => $allSettings,
        ]);
    }

    public function initialize(): JsonResponse
    {
        $defaults = [
            ['key' => 'site_name', 'value' => 'CartFlow Store', 'type' => 'string', 'category' => 'general', 'sort_order' => 1],
            ['key' => 'site_email', 'value' => 'admin@cartflow.com', 'type' => 'string', 'category' => 'general', 'sort_order' => 2],
            ['key' => 'site_phone', 'value' => '+1234567890', 'type' => 'string', 'category' => 'general', 'sort_order' => 3],
            ['key' => 'site_address', 'value' => '', 'type' => 'string', 'category' => 'general', 'sort_order' => 4],
            ['key' => 'currency_code', 'value' => 'USD', 'type' => 'string', 'category' => 'currency', 'sort_order' => 1],
            ['key' => 'currency_symbol', 'value' => '$', 'type' => 'string', 'category' => 'currency', 'sort_order' => 2],
            ['key' => 'currency_position', 'value' => 'left', 'type' => 'string', 'category' => 'currency', 'sort_order' => 3],
            ['key' => 'decimal_places', 'value' => 2, 'type' => 'integer', 'category' => 'currency', 'sort_order' => 4],
            ['key' => 'default_tax_rate', 'value' => 10, 'type' => 'integer', 'category' => 'tax', 'sort_order' => 1],
            ['key' => 'tax_included', 'value' => false, 'type' => 'boolean', 'category' => 'tax', 'sort_order' => 2],
            ['key' => 'enable_shipping', 'value' => true, 'type' => 'boolean', 'category' => 'shipping', 'sort_order' => 1],
            ['key' => 'free_shipping_threshold', 'value' => 100, 'type' => 'integer', 'category' => 'shipping', 'sort_order' => 2],
            ['key' => 'flat_rate_shipping', 'value' => 10, 'type' => 'integer', 'category' => 'shipping', 'sort_order' => 3],
            ['key' => 'enable_paypal', 'value' => true, 'type' => 'boolean', 'category' => 'payment', 'sort_order' => 1],
            ['key' => 'paypal_email', 'value' => '', 'type' => 'string', 'category' => 'payment', 'sort_order' => 2],
            ['key' => 'enable_stripe', 'value' => true, 'type' => 'boolean', 'category' => 'payment', 'sort_order' => 3],
            ['key' => 'stripe_key', 'value' => '', 'type' => 'string', 'category' => 'payment', 'sort_order' => 4],
            ['key' => 'stripe_secret', 'value' => '', 'type' => 'string', 'category' => 'payment', 'sort_order' => 5],
            ['key' => 'enable_cod', 'value' => true, 'type' => 'boolean', 'category' => 'payment', 'sort_order' => 6],
        ];

        foreach ($defaults as $default) {
            Setting::updateOrCreate(
                ['key' => $default['key']],
                $default
            );
        }

        return response()->json(['message' => 'Default settings initialized']);
    }
}