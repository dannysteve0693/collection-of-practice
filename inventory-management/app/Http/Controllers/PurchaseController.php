<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\Purchase;
use App\Models\ActivityLog;
use Illuminate\Http\Request;

class PurchaseController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $purchases = Purchase::with(['item', 'user'])->paginate(10);
        return view('purchases.index', compact('purchases'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $items = Item::all();
        return view('purchases.create', compact('items'));
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

        $purchase = Purchase::create([
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'user_id' => auth()->id(),
        ]);

        $item = Item::find($request->item_id);
        $item->increment('quantity', $request->quantity);
        
        ActivityLog::logActivity('created', $purchase, $purchase->id, null, $purchase->toArray());

        return redirect()->route('purchases.index')->with('success', 'Purchase recorded successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Purchase $purchase)
    {
        $purchase->load(['item', 'user']);
        return view('purchases.show', compact('purchase'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Purchase $purchase)
    {
        $items = Item::all();
        return view('purchases.edit', compact('purchase', 'items'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Purchase $purchase)
    {
        $request->validate([
            'item_id' => 'required|exists:items,id',
            'quantity' => 'required|integer|min:1',
        ]);

        $oldValues = $purchase->toArray();
        $oldQuantity = $purchase->quantity;
        $oldItemId = $purchase->item_id;

        $item = Item::find($oldItemId);
        $item->decrement('quantity', $oldQuantity);

        $purchase->update([
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
        ]);

        $newItem = Item::find($request->item_id);
        $newItem->increment('quantity', $request->quantity);
        
        ActivityLog::logActivity('updated', $purchase, $purchase->id, $oldValues, $purchase->toArray());

        return redirect()->route('purchases.index')->with('success', 'Purchase updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Purchase $purchase)
    {
        $oldValues = $purchase->toArray();
        
        $item = Item::find($purchase->item_id);
        $item->decrement('quantity', $purchase->quantity);
        
        $purchase->delete();
        
        ActivityLog::logActivity('deleted', $purchase, $purchase->id, $oldValues, null);

        return redirect()->route('purchases.index')->with('success', 'Purchase deleted successfully.');
    }
}
