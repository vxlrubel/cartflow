<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Analytics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AnalyticsController extends Controller
{
    public function overview(): JsonResponse
    {
        $today = now()->toDateString();
        $data = Analytics::where('date', $today)->first();

        return response()->json([
            'total_sales' => $data?->total_sales ?? 0,
            'total_orders' => $data?->total_orders ?? 0,
            'total_customers' => $data?->total_customers ?? 0,
        ]);
    }

    public function revenue(Request $request): JsonResponse
    {
        $type = $request->type ?? 'daily';
        $start = match ($type) {
            'monthly' => now()->startOfMonth(),
            'yearly' => now()->startOfYear(),
            default => now()->startOfDay(),
        };

        $data = Analytics::where('date', '>=', $start)->get();

        return response()->json([
            'type' => $type,
            'data' => $data->map(fn ($d) => [
                'date' => $d->date,
                'sales' => $d->total_sales,
                'orders' => $d->total_orders,
            ]),
        ]);
    }

    public function products(): JsonResponse
    {
        return response()->json(['message' => 'Product analytics - implement as needed']);
    }

    public function sales(Request $request): JsonResponse
    {
        $type = $request->type ?? 'daily';
        $start = match ($type) {
            'weekly' => now()->subWeek(),
            'monthly' => now()->subMonth(),
            'yearly' => now()->subYear(),
            default => now()->startOfDay(),
        };

        $sales = Analytics::where('date', '>=', $start)->selectRaw('SUM(total_sales) as sales, date')->groupBy('date')->get();

        return response()->json($sales);
    }

    public function orders(): JsonResponse
    {
        $orders = Analytics::selectRaw('SUM(total_orders) as total, date')->groupBy('date')->get();

        return response()->json($orders);
    }

    public function customers(): JsonResponse
    {
        $customers = Analytics::selectRaw('SUM(total_customers) as total, date')->groupBy('date')->get();

        return response()->json($customers);
    }
}
