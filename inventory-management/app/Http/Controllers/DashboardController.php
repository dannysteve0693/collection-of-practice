<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $totalItems = Item::count();
        $totalPurchasesToday = Purchase::whereDate('created_at', today())->sum('quantity');
        $totalSalesToday = Sale::whereDate('created_at', today())->sum('quantity');
        $lowStockItems = Item::where('quantity', '<=', 10)->count();
        
        $recentActivities = ActivityLog::with('user')
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();
            
        $topItems = Item::orderBy('quantity', 'desc')->limit(5)->get();
        
        return view('dashboard', compact(
            'totalItems',
            'totalPurchasesToday',
            'totalSalesToday',
            'lowStockItems',
            'recentActivities',
            'topItems'
        ));
    }
}