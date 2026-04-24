<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Analytics;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AnalyticsController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min((int) ($request->per_page ?? 15), 50);
        $sortBy = $request->sort_by ?? 'date';
        $sortOrder = $request->sort_order ?? 'desc';

        $query = Analytics::query();

        if ($request->filled('start_date')) {
            $query->where('date', '>=', $request->start_date);
        }
        if ($request->filled('end_date')) {
            $query->where('date', '<=', $request->end_date);
        }

        $allowedSorts = ['date', 'total_sales', 'total_orders', 'total_customers'];
        if (in_array($sortBy, $allowedSorts)) {
            $query->orderBy($sortBy, $sortOrder === 'asc' ? 'asc' : 'desc');
        }

        if ($request->boolean('trashed')) {
            $query->onlyTrashed();
        }

        $analytics = $query->paginate($perPage);

        return response()->json($analytics);
    }

    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'total_sales' => 'required|numeric|min:0',
            'total_orders' => 'required|integer|min:0',
            'total_customers' => 'required|integer|min:0',
            'date' => 'required|date',
        ]);

        $analytics = Analytics::create([
            'total_sales' => $request->total_sales,
            'total_orders' => $request->total_orders,
            'total_customers' => $request->total_customers,
            'date' => $request->date,
        ]);

        return response()->json($analytics, 201);
    }

    public function show(int $id): JsonResponse
    {
        $analytics = Analytics::findOrFail($id);

        return response()->json($analytics);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $analytics = Analytics::findOrFail($id);

        $request->validate([
            'total_sales' => 'sometimes|numeric|min:0',
            'total_orders' => 'sometimes|integer|min:0',
            'total_customers' => 'sometimes|integer|min:0',
            'date' => 'sometimes|date',
        ]);

        $analytics->update($request->only(['total_sales', 'total_orders', 'total_customers', 'date']));

        return response()->json($analytics);
    }

    public function destroy(int $id): JsonResponse
    {
        $analytics = Analytics::findOrFail($id);
        $analytics->delete();

        return response()->json(['message' => 'Analytics record moved to trash']);
    }

    public function restore(int $id): JsonResponse
    {
        $analytics = Analytics::onlyTrashed()->findOrFail($id);
        $analytics->restore();

        return response()->json($analytics);
    }

    public function forceDelete(int $id): JsonResponse
    {
        $analytics = Analytics::onlyTrashed()->findOrFail($id);
        $analytics->forceDelete();

        return response()->json(['message' => 'Analytics record permanently deleted']);
    }

    public function trash(): JsonResponse
    {
        $analytics = Analytics::onlyTrashed()->orderBy('deleted_at', 'desc')->paginate(15);

        return response()->json($analytics);
    }

    public function overview(): JsonResponse
    {
        $today = now()->toDateString();
        $thisMonth = now()->startOfMonth();
        $thisYear = now()->startOfYear();

        $todayData = Analytics::where('date', $today)->first();
        $monthData = Analytics::where('date', '>=', $thisMonth)->get();
        $yearData = Analytics::where('date', '>=', $thisYear)->get();

        $todayOrders = Order::whereDate('created_at', $today)->count();
        $monthOrders = Order::where('created_at', '>=', $thisMonth)->count();
        $yearOrders = Order::where('created_at', '>=', $thisYear)->count();

        $todayRevenue = Order::whereDate('created_at', $today)
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        $monthRevenue = Order::where('created_at', '>=', $thisMonth)
            ->where('payment_status', 'paid')
            ->sum('total_amount');
        $yearRevenue = Order::where('created_at', '>=', $thisYear)
            ->where('payment_status', 'paid')
            ->sum('total_amount');

        $totalCustomers = User::count();
        $newCustomersThisMonth = User::where('created_at', '>=', $thisMonth)->count();
        $newCustomersThisYear = User::where('created_at', '>=', $thisYear)->count();

        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::whereIn('status', ['paid', 'shipped'])->count();
        $deliveredOrders = Order::where('status', 'delivered')->count();

        $averageOrderValue = $monthOrders > 0 ? $monthRevenue / $monthOrders : 0;

        return response()->json([
            'today' => [
                'sales' => $todayData?->total_sales ?? $todayRevenue,
                'orders' => $todayData?->total_orders ?? $todayOrders,
                'customers' => $todayData?->total_customers ?? 0,
            ],
            'month' => [
                'sales' => $monthData->sum('total_sales') ?? $monthRevenue,
                'orders' => $monthOrders,
                'customers' => $monthData->sum('total_customers') ?? $newCustomersThisMonth,
                'revenue' => $monthRevenue,
            ],
            'year' => [
                'sales' => $yearData->sum('total_sales') ?? $yearRevenue,
                'orders' => $yearOrders,
                'customers' => $yearData->sum('total_customers') ?? $newCustomersThisYear,
                'revenue' => $yearRevenue,
            ],
            'totals' => [
                'customers' => $totalCustomers,
                'new_customers_month' => $newCustomersThisMonth,
                'new_customers_year' => $newCustomersThisYear,
            ],
            'order_status' => [
                'pending' => $pendingOrders,
                'processing' => $processingOrders,
                'delivered' => $deliveredOrders,
            ],
            'average_order_value' => round($averageOrderValue, 2),
            'conversion_rate' => round($totalCustomers > 0 ? ($monthOrders / $totalCustomers) * 100 : 0, 2),
        ]);
    }

    public function revenue(Request $request): JsonResponse
    {
        $type = $request->type ?? 'daily';
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $start = match ($type) {
            'weekly' => $startDate ? Carbon::parse($startDate) : now()->subWeek(),
            'monthly' => $startDate ? Carbon::parse($startDate) : now()->subMonth(),
            'yearly' => $startDate ? Carbon::parse($startDate) : now()->subYear(),
            default => $startDate ? Carbon::parse($startDate) : now()->startOfDay(),
        };

        $end = $endDate ? Carbon::parse($endDate) : now();

        $orders = Order::whereBetween('created_at', [$start, $end])
            ->where('payment_status', 'paid')
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->get();

        $totalRevenue = $orders->sum('revenue');
        $totalOrders = $orders->sum('orders');
        $averageOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        $byStatus = Order::whereBetween('created_at', [$start, $end])
            ->where('payment_status', 'paid')
            ->selectRaw('status, COUNT(*) as count, SUM(total_amount) as revenue')
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn ($item) => [$item->status => ['count' => $item->count, 'revenue' => $item->revenue]]);

        return response()->json([
            'type' => $type,
            'data' => $orders,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_orders' => $totalOrders,
                'average_order_value' => round($averageOrder, 2),
            ],
            'by_status' => $byStatus,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ]);
    }

    public function products(Request $request): JsonResponse
    {
        $type = $request->type ?? 'monthly';
        $limit = min((int) ($request->limit ?? 10), 50);
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $start = $startDate ? Carbon::parse($startDate) : now()->subMonth();
        $end = $endDate ? Carbon::parse($endDate) : now();

        $productStats = OrderItem::whereHas('order', fn ($q) => $q
            ->whereBetween('created_at', [$start, $end])
            ->where('payment_status', 'paid'))
            ->with('product:id,name,sku,price')
            ->get()
            ->groupBy('product_id')
            ->map(fn ($items) => [
                'product_id' => $items->first()->product_id,
                'product_name' => $items->first()->product?->name ?? 'Unknown',
                'product_sku' => $items->first()->product?->sku ?? 'N/A',
                'quantity_sold' => $items->sum('quantity'),
                'revenue' => $items->sum(fn ($item) => $item->price * $item->quantity),
            ])
            ->sortByDesc('revenue')
            ->take($limit)
            ->values();

        $totalRevenue = $productStats->sum('revenue');
        $totalQuantity = $productStats->sum('quantity_sold');

        $topProducts = $productStats->take(5);
        $lowPerforming = $productStats->sortBy('revenue')->take(5);

        return response()->json([
            'type' => $type,
            'products' => $productStats,
            'top_performers' => $topProducts,
            'low_performers' => $lowPerforming,
            'summary' => [
                'total_revenue' => $totalRevenue,
                'total_quantity_sold' => $totalQuantity,
                'average_product_revenue' => $productStats->count() > 0 ? round($totalRevenue / $productStats->count(), 2) : 0,
            ],
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ]);
    }

    public function sales(Request $request): JsonResponse
    {
        $type = $request->type ?? 'daily';
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $start = match ($type) {
            'weekly' => $startDate ? Carbon::parse($startDate) : now()->subWeek(),
            'monthly' => $startDate ? Carbon::parse($startDate) : now()->subMonth(),
            'yearly' => $startDate ? Carbon::parse($startDate) : now()->subYear(),
            default => $startDate ? Carbon::parse($startDate) : now()->startOfDay(),
        };

        $end = $endDate ? Carbon::parse($endDate) : now();

        $sales = Order::whereBetween('created_at', [$start, $end])
            ->where('payment_status', 'paid')
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders, SUM(total_amount) as sales')
            ->groupBy('date')
            ->get();

        $totalSales = $sales->sum('sales');
        $totalOrders = $sales->sum('orders');
        $averageOrder = $totalOrders > 0 ? $totalSales / $totalOrders : 0;

        $dailyAverage = $sales->count() > 0 ? $totalSales / $sales->count() : 0;
        $growthRate = $this->calculateGrowthRate($sales);

        return response()->json([
            'type' => $type,
            'data' => $sales,
            'summary' => [
                'total_sales' => $totalSales,
                'total_orders' => $totalOrders,
                'average_order_value' => round($averageOrder, 2),
                'daily_average' => round($dailyAverage, 2),
                'growth_rate' => round($growthRate, 2),
            ],
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $type = $request->type ?? 'monthly';
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $start = match ($type) {
            'weekly' => $startDate ? Carbon::parse($startDate) : now()->subWeek(),
            'monthly' => $startDate ? Carbon::parse($startDate) : now()->subMonth(),
            'yearly' => $startDate ? Carbon::parse($startDate) : now()->subYear(),
            default => $startDate ? Carbon::parse($startDate) : now()->startOfDay(),
        };

        $end = $endDate ? Carbon::parse($endDate) : now();

        $orders = Order::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as orders')
            ->groupBy('date')
            ->get();

        $statusCounts = Order::whereBetween('created_at', [$start, $end])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        $totalOrders = $orders->sum('orders');
        $cancellationRate = $totalOrders > 0 ? ($statusCounts['cancelled'] ?? 0) / $totalOrders * 100 : 0;

        return response()->json([
            'type' => $type,
            'data' => $orders,
            'summary' => [
                'total_orders' => $totalOrders,
                'cancellation_rate' => round($cancellationRate, 2),
            ],
            'status_breakdown' => $statusCounts,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ]);
    }

    public function customers(Request $request): JsonResponse
    {
        $type = $request->type ?? 'monthly';
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $start = match ($type) {
            'weekly' => $startDate ? Carbon::parse($startDate) : now()->subWeek(),
            'monthly' => $startDate ? Carbon::parse($startDate) : now()->subMonth(),
            'yearly' => $startDate ? Carbon::parse($startDate) : now()->subYear(),
            default => $startDate ? Carbon::parse($startDate) : now()->startOfDay(),
        };

        $end = $endDate ? Carbon::parse($endDate) : now();

        $newCustomers = User::whereBetween('created_at', [$start, $end])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as new_customers')
            ->groupBy('date')
            ->get();

        $totalNewCustomers = $newCustomers->sum('new_customers');
        $totalCustomers = User::count();

        $repeatCustomers = User::whereHas('orders', fn ($q) => $q
            ->whereBetween('created_at', [$start, $end])
            ->where('payment_status', 'paid'))
            ->withCount('orders')
            ->get()
            ->filter(fn ($user) => $user->orders_count > 1)
            ->count();

        $topCustomers = User::whereHas('orders', fn ($q) => $q
            ->whereBetween('created_at', [$start, $end])
            ->where('payment_status', 'paid'))
            ->withSum(['orders' => fn ($q) => $q->whereBetween('created_at', [$start, $end])->where('payment_status', 'paid')], 'total_amount')
            ->orderByDesc('orders_sum_total_amount')
            ->take(10)
            ->get()
            ->map(fn ($user) => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'total_orders' => $user->orders_count,
                'total_revenue' => $user->orders_sum_total_amount ?? 0,
            ]);

        return response()->json([
            'type' => $type,
            'new_customers' => $newCustomers,
            'summary' => [
                'total_customers' => $totalCustomers,
                'new_customers' => $totalNewCustomers,
                'repeat_customers' => $repeatCustomers,
                'repeat_rate' => $totalNewCustomers > 0 ? round($repeatCustomers / $totalNewCustomers * 100, 2) : 0,
            ],
            'top_customers' => $topCustomers,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ]);
    }

    private function calculateGrowthRate($data): float
    {
        if ($data->count() < 2) {
            return 0;
        }

        $sorted = $data->sortBy('date');
        $first = $sorted->first()->sales ?? 0;
        $last = $sorted->last()->sales ?? 0;

        if ($first == 0) {
            return 0;
        }

        return (($last - $first) / $first) * 100;
    }
}