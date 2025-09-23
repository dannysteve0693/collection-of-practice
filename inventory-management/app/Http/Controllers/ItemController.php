<?php

namespace App\Http\Controllers;

use App\Models\Item;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = Item::paginate(10);
        return view('items.index', compact('items'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('items.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:items,sku',
            'quantity' => 'required|integer|min:0',
        ]);

        $item = Item::create($request->all());
        
        ActivityLog::logActivity('created', $item, $item->id, null, $item->toArray());

        return redirect()->route('items.index')->with('success', 'Item created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(Item $item)
    {
        $item->load(['purchases.user', 'sales.user']);
        return view('items.show', compact('item'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Item $item)
    {
        return view('items.edit', compact('item'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Item $item)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|string|max:255|unique:items,sku,' . $item->id,
            'quantity' => 'required|integer|min:0',
        ]);

        $oldValues = $item->toArray();
        $item->update($request->all());
        
        ActivityLog::logActivity('updated', $item, $item->id, $oldValues, $item->toArray());

        return redirect()->route('items.index')->with('success', 'Item updated successfully.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Item $item)
    {
        $oldValues = $item->toArray();
        $item->delete();
        
        ActivityLog::logActivity('deleted', $item, $item->id, $oldValues, null);

        return redirect()->route('items.index')->with('success', 'Item deleted successfully.');
    }

    public function bulkUpload()
    {
        return view('items.bulk-upload');
    }

    public function processBulkUpload(Request $request)
    {
        $request->validate([
            'csv_file' => 'required|file|mimes:csv,txt|max:2048',
        ]);

        $file = $request->file('csv_file');
        $csvData = file_get_contents($file);
        $rows = array_map('str_getcsv', explode("\n", $csvData));
        $header = array_shift($rows);
        
        $successCount = 0;
        $errorCount = 0;
        $errors = [];

        foreach ($rows as $index => $row) {
            if (empty(array_filter($row))) continue;
            
            try {
                $data = array_combine($header, $row);
                
                if (!isset($data['name']) || !isset($data['sku']) || !isset($data['quantity'])) {
                    throw new \Exception('Missing required fields');
                }

                $item = Item::create([
                    'name' => $data['name'],
                    'sku' => $data['sku'],
                    'quantity' => (int)$data['quantity'],
                ]);
                
                ActivityLog::logActivity('created', $item, $item->id, null, $item->toArray());
                $successCount++;
            } catch (\Exception $e) {
                $errorCount++;
                $errors[] = "Row " . ($index + 2) . ": " . $e->getMessage();
            }
        }

        $message = "Import completed. {$successCount} items imported successfully.";
        if ($errorCount > 0) {
            $message .= " {$errorCount} errors occurred.";
        }

        return redirect()->route('items.index')
            ->with('success', $message)
            ->with('errors', $errors);
    }
}
