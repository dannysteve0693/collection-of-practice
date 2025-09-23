<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Sale;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $sales = Sale::with(['item', 'user'])->paginate(10);
        return view('sales.index', compact('sales'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $items = Item::where('quantity', '>', 0)->get();
        return view('sales.create', compact('items'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $item = Item::find($request->item_id);
        
        if ($item->quantity < $request->quantity) {
            return back()->withErrors(['quantity' => 'Insufficient stock. Available quantity: ' . $item->quantity]);
        }

        $sale = Sale::create([
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'user_id' => auth()->id(),
        ]);

        $item->decrement('quantity', $request->quantity);
        
        ActivityLog::logActivity('created', $sale, $sale->id, null, $sale->toArray());

        return redirect()->route('sales.index')->with('success', 'Sale recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Sale $sale)
    {
        $sale->load(['item', 'user']);
        return view('sales.show', compact('sale'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Sale $sale)
    {
        $items = Item::all();
        return view('sales.edit', compact('sale', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Sale $sale)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $oldValues = $sale->toArray();
        $oldQuantity = $sale->quantity;
        $oldItemId = $sale->item_id;

        // Restore old quantity
        $oldItem = Item::find($oldItemId);
        $oldItem->increment('quantity', $oldQuantity);

        // Check new item stock
        $newItem = Item::find($request->item_id);
        if ($newItem->quantity < $request->quantity) {
            $oldItem->decrement('quantity', $oldQuantity); // Revert
            return back()->withErrors(['quantity' => 'Insufficient stock. Available quantity: ' . $newItem->quantity]);
        }

        $sale->update([
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
        ]);

        $newItem->decrement('quantity', $request->quantity);
        
        ActivityLog::logActivity('updated', $sale, $sale->id, $oldValues, $sale->toArray());

        return redirect()->route('sales.index')->with('success', 'Sale updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Sale $sale)
    {
        $oldValues = $sale->toArray();
        
        $item = Item::find($sale->item_id);
        $item->increment('quantity', $sale->quantity);
        
        $sale->delete();
        
        ActivityLog::logActivity('deleted', $sale, $sale->id, $oldValues, null);

        return redirect()->route('sales.index')->with('success', 'Sale deleted successfully.');
    }
}