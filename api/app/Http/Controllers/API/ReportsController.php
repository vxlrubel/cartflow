<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportsController extends Controller
{
    public function period(Request $request): JsonResponse
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
            ->selectRaw('DATE(created_at) as date, COUNT(*) as total_orders, SUM(total_amount) as total_revenue')
            ->groupBy('date')
            ->get();

        $summary = [
            'total_orders' => $orders->sum('total_orders'),
            'total_revenue' => $orders->sum('total_revenue'),
            'average_order' => $orders->avg('total_revenue'),
            'period' => $type,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ];

        return response()->json([
            'period' => $type,
            'data' => $orders,
            'summary' => $summary,
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

        $revenue = Order::whereBetween('created_at', [$start, $end])
            ->whereIn('status', ['delivered', 'paid'])
            ->where('payment_status', 'paid')
            ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->get();

        $totalRevenue = $revenue->sum('revenue');
        $totalOrders = $revenue->sum('orders');
        $averageOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return response()->json([
            'type' => $type,
            'data' => $revenue,
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'average_order_value' => round($averageOrder, 2),
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ]);
    }

    public function orders(Request $request): JsonResponse
    {
        $startDate = $request->start_date;
        $endDate = $request->end_date;
        $status = $request->status;

        $query = Order::with(['user']);

        if ($startDate && $endDate) {
            $query->whereBetween('created_at', [Carbon::parse($startDate), Carbon::parse($endDate)]);
        }

        if ($status) {
            $query->where('status', $status);
        }

        $orders = $query->orderBy('created_at', 'desc')->paginate($request->per_page ?? 15);

        $statusCounts = Order::selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->get()
            ->pluck('count', 'status');

        return response()->json([
            'orders' => $orders,
            'status_counts' => $statusCounts,
        ]);
    }

    public function taxes(Request $request): JsonResponse
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
            ->where('payment_status', 'paid')
            ->get();

        $taxRate = 0.10;
        $subtotal = $orders->sum('total_amount');
        $taxAmount = $subtotal * $taxRate;
        $totalWithTax = $subtotal + $taxAmount;

        $byStatus = $orders->groupBy('status')->map(function ($group) use ($taxRate) {
            $subtotal = $group->sum('total_amount');
            return [
                'count' => $group->count(),
                'subtotal' => $subtotal,
                'tax' => $subtotal * $taxRate,
                'total' => $subtotal * (1 + $taxRate),
            ];
        });

        return response()->json([
            'type' => $type,
            'subtotal' => round($subtotal, 2),
            'tax_rate' => $taxRate * 100 . '%',
            'tax_amount' => round($taxAmount, 2),
            'total_with_tax' => round($totalWithTax, 2),
            'tax_by_status' => $byStatus,
            'start_date' => $start->toDateString(),
            'end_date' => $end->toDateString(),
        ]);
    }

    public function export(Request $request): JsonResponse
    {
        $type = $request->type ?? 'orders';
        $format = $request->format ?? 'csv';
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $start = $startDate ? Carbon::parse($startDate) : now()->subMonth();
        $end = $endDate ? Carbon::parse($endDate) : now();

        switch ($type) {
            case 'revenue':
                $data = Order::whereBetween('created_at', [$start, $end])
                    ->where('payment_status', 'paid')
                    ->selectRaw('DATE(created_at) as date, SUM(total_amount) as revenue, COUNT(*) as orders')
                    ->groupBy('date')
                    ->get();
                break;

            case 'orders':
                $data = Order::whereBetween('created_at', [$start, $end])
                    ->with(['user'])
                    ->get()
                    ->map(fn ($order) => [
                        'id' => $order->id,
                        'order_number' => $order->order_number,
                        'customer' => $order->user?->name ?? 'N/A',
                        'email' => $order->user?->email ?? 'N/A',
                        'total' => $order->total_amount,
                        'status' => $order->status,
                        'payment_status' => $order->payment_status,
                        'date' => $order->created_at->toDateString(),
                    ]);
                break;

            case 'products':
                $data = \App\Models\OrderItem::whereHas('order', fn ($q) => $q->whereBetween('created_at', [$start, $end]))
                    ->with('product')
                    ->get()
                    ->groupBy('product_id')
                    ->map(fn ($items, $productId) => [
                        'product_id' => $productId,
                        'product_name' => $items->first()->product?->name ?? 'N/A',
                        'quantity_sold' => $items->sum('quantity'),
                        'revenue' => $items->sum(fn ($item) => $item->price * $item->quantity),
                    ])
                    ->values();
                break;

            case 'customers':
                $data = \App\Models\User::whereHas('orders', fn ($q) => $q->whereBetween('created_at', [$start, $end]))
                    ->withCount('orders')
                    ->get()
                    ->map(fn ($user) => [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'total_orders' => $user->orders_count,
                    ]);
                break;

            default:
                return response()->json(['error' => 'Invalid export type'], 400);
        }

        if ($format === 'csv') {
            $csvData = $this->generateCsv($data);
            return response()->json([
                'type' => $type,
                'format' => $format,
                'data' => $csvData,
                'download_url' => '/exports/' . $type . '_' . now()->toDateString() . '.csv',
            ]);
        }

        if ($format === 'pdf') {
            $pdfData = $this->generatePdf($data, ucfirst($type) . ' Report');
            return response()->json([
                'type' => $type,
                'format' => $format,
                'data' => $pdfData,
                'download_url' => '/exports/' . $type . '_' . now()->toDateString() . '.html',
            ]);
        }

        return response()->json([
            'type' => $type,
            'format' => $format,
            'data' => $data,
            'count' => $data->count(),
            'period' => ['start' => $start->toDateString(), 'end' => $end->toDateString()],
        ]);
    }

    private function generateCsv($data): string
    {
        $data = collect($data);
        
        if ($data->isEmpty()) {
            return '';
        }

        $firstItem = $data->first();
        $headers = is_array($firstItem) ? array_keys($firstItem) : array_keys($firstItem->toArray() ?? []);
        $csv = implode(',', $headers) . "\n";

        foreach ($data as $row) {
            $values = is_array($row) ? array_values($row) : array_values($row->toArray() ?? []);
            $csv .= implode(',', $values) . "\n";
        }

        return $csv;
    }

    private function generatePdf($data, string $title = 'Report'): string
    {
        $data = collect($data);
        
        if ($data->isEmpty()) {
            return '<html><body><p>No data found</p></body></html>';
        }

        $firstItem = $data->first();
        $headers = is_array($firstItem) ? array_keys($firstItem) : array_keys($firstItem->toArray() ?? []);
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>' . $title . '</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; }
        h1 { color: #333; }
        table { width: 100%; border-collapse: collapse; margin-top: 20px; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background-color: #f5f5f5; }
        tr:nth-child(even) { background-color: #fafafa; }
        .total { font-weight: bold; background-color: #e8f5e9; }
    </style>
</head>
<body>
    <h1>' . $title . '</h1>
    <p>Generated: ' . now()->toDateTimeString() . '</p>
    <table>
        <thead>
            <tr>';
        
        foreach ($headers as $header) {
            $html .= '<th>' . ucwords(str_replace('_', ' ', $header)) . '</th>';
        }
        
        $html .= '</tr>
        </thead>
        <tbody>';
        
        foreach ($data as $row) {
            $values = is_array($row) ? array_values($row) : array_values($row->toArray() ?? []);
            $html .= '<tr>';
            foreach ($values as $value) {
                $html .= '<td>' . htmlspecialchars($value ?? '') . '</td>';
            }
            $html .= '</tr>';
        }
        
        $html .= '</tbody>
    </table>
    <p style="margin-top: 20px; color: #666;">Total records: ' . $data->count() . '</p>
</body>
</html>';

        return $html;
    }

    public function summary(): JsonResponse
    {
        $today = now()->toDateString();
        $thisMonth = now()->startOfMonth();
        $thisYear = now()->startOfYear();

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

        $pendingOrders = Order::where('status', 'pending')->count();
        $processingOrders = Order::whereIn('status', ['paid', 'shipped'])->count();

        return response()->json([
            'today' => [
                'orders' => $todayOrders,
                'revenue' => $todayRevenue,
            ],
            'month' => [
                'orders' => $monthOrders,
                'revenue' => $monthRevenue,
            ],
            'year' => [
                'orders' => $yearOrders,
                'revenue' => $yearRevenue,
            ],
            'order_status' => [
                'pending' => $pendingOrders,
                'processing' => $processingOrders,
            ],
        ]);
    }
}