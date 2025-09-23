<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\Sale;
use Illuminate\Http\Request;
use Carbon\Carbon;

class ReportController extends Controller
{
    public function index()
    {
        return view('reports.index');
    }

    public function monthly(Request $request)
    {
        $month = $request->input('month', now()->format('Y-m'));
        $startDate = Carbon::createFromFormat('Y-m', $month)->startOfMonth();
        $endDate = Carbon::createFromFormat('Y-m', $month)->endOfMonth();

        $items = Item::with(['purchases' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }, 'sales' => function($query) use ($startDate, $endDate) {
            $query->whereBetween('created_at', [$startDate, $endDate]);
        }])->get();

        $totalPurchases = Purchase::whereBetween('created_at', [$startDate, $endDate])->sum('quantity');
        $totalSales = Sale::whereBetween('created_at', [$startDate, $endDate])->sum('quantity');

        $reportData = [
            'month' => $month,
            'items' => $items,
            'totalPurchases' => $totalPurchases,
            'totalSales' => $totalSales,
            'generatedAt' => now(),
        ];

        if ($request->input('export') === 'pdf') {
            return $this->exportToPdf($reportData);
        }

        return view('reports.monthly', $reportData);
    }

    public function exportToPdf($data)
    {
        // For now, return a simple text response
        // In a real application, you would use a package like barryvdh/laravel-dompdf
        $content = "Monthly Inventory Report - " . $data['month'] . "\n";
        $content .= "Generated on: " . $data['generatedAt']->format('Y-m-d H:i:s') . "\n\n";
        $content .= "Total Purchases: " . $data['totalPurchases'] . "\n";
        $content .= "Total Sales: " . $data['totalSales'] . "\n\n";
        $content .= "Items:\n";
        
        foreach ($data['items'] as $item) {
            $monthlyPurchases = $item->purchases->sum('quantity');
            $monthlySales = $item->sales->sum('quantity');
            $content .= "- {$item->name} (SKU: {$item->sku}): Stock: {$item->quantity}, Purchased: {$monthlyPurchases}, Sold: {$monthlySales}\n";
        }

        return response($content, 200, [
            'Content-Type' => 'text/plain',
            'Content-Disposition' => 'attachment; filename="monthly-report-' . $data['month'] . '.txt"'
        ]);
    }

    public function inventory()
    {
        $items = Item::with(['purchases', 'sales'])->get();
        
        $lowStockItems = $items->filter(function ($item) {
            return $item->quantity <= 10; // Define low stock threshold
        });

        return view('reports.inventory', compact('items', 'lowStockItems'));
    }

    public function activityLogs()
    {
        $logs = \App\Models\ActivityLog::with('user')->orderBy('created_at', 'desc')->paginate(20);
        return view('reports.activity-logs', compact('logs'));
    }
}