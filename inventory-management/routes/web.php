<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\ItemController;
use App\Http\Controllers\PurchaseController;
use App\Http\Controllers\SaleController;
use App\Http\Controllers\ReportController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/dashboard');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');
    
    // Profile routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
    
    // Items routes - Admin and Purchasers can manage items
    Route::middleware(['role:admin,purchaser'])->group(function () {
        Route::resource('items', ItemController::class);
        Route::get('/items/bulk/upload', [ItemController::class, 'bulkUpload'])->name('items.bulk-upload');
        Route::post('/items/bulk/process', [ItemController::class, 'processBulkUpload'])->name('items.process-bulk-upload');
    });
    
    // View-only items for other roles
    Route::middleware(['role:sales,viewer'])->group(function () {
        Route::get('/items', [ItemController::class, 'index'])->name('items.index');
        Route::get('/items/{item}', [ItemController::class, 'show'])->name('items.show');
    });
    
    // Purchases routes - Admin and Purchasers only
    Route::middleware(['role:admin,purchaser'])->group(function () {
        Route::resource('purchases', PurchaseController::class);
    });
    
    // Sales routes - Admin and Sales only
    Route::middleware(['role:admin,sales'])->group(function () {
        Route::resource('sales', SaleController::class);
    });
    
    // Reports routes - All authenticated users can view reports
    Route::prefix('reports')->name('reports.')->group(function () {
        Route::get('/', [ReportController::class, 'index'])->name('index');
        Route::get('/monthly', [ReportController::class, 'monthly'])->name('monthly');
        Route::get('/inventory', [ReportController::class, 'inventory'])->name('inventory');
        Route::get('/activity-logs', [ReportController::class, 'activityLogs'])->name('activity-logs');
    });
});

require __DIR__.'/auth.php';
