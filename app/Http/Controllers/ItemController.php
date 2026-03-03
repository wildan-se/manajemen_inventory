<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\Item;
use App\Models\Unit;
use Illuminate\Http\Request;

class ItemController extends Controller
{
  public function index(Request $request)
  {
    $query = Item::with('category', 'unit')
      ->when($request->search, fn($q) => $q->where('name', 'like', "%{$request->search}%")
        ->orWhere('code', 'like', "%{$request->search}%"))
      ->when($request->category_id, fn($q) => $q->where('category_id', $request->category_id))
      ->when($request->is_active !== null && $request->is_active !== '', fn($q) => $q->where('is_active', $request->is_active));

    $items      = $query->latest()->paginate(20)->withQueryString();
    $categories = Category::orderBy('name')->get();
    return view('items.index', compact('items', 'categories'));
  }

  public function create()
  {
    $categories = Category::orderBy('name')->get();
    $units      = Unit::orderBy('name')->get();
    return view('items.create', compact('categories', 'units'));
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'code'        => 'required|string|max:50|unique:items,code',
      'name'        => 'required|string|max:255',
      'category_id' => 'required|exists:categories,id',
      'unit_id'     => 'required|exists:units,id',
      'description' => 'nullable|string|max:1000',
      'min_stock'   => 'required|numeric|min:0',
      'max_stock'   => 'nullable|numeric|min:0',
      'is_active'   => 'boolean',
    ]);

    $data['is_active'] = $request->boolean('is_active', true);
    Item::create($data);
    return redirect()->route('items.index')
      ->with($data['is_active'] ? 'success' : 'warning', $data['is_active'] ? 'Item berhasil diaktifkan' : 'Item berhasil dinonaktifkan');
  }

  public function show(Item $item)
  {
    $item->load('category', 'unit');
    $stocks          = $item->stocks()->with('warehouse', 'location')->get();
    $recentMovements = $item->stockMovements()->with('user', 'toWarehouse', 'fromWarehouse')
      ->latest()->take(20)->get();
    return view('items.show', compact('item', 'stocks', 'recentMovements'));
  }

  public function edit(Item $item)
  {
    $categories = Category::orderBy('name')->get();
    $units      = Unit::orderBy('name')->get();
    return view('items.edit', compact('item', 'categories', 'units'));
  }

  public function update(Request $request, Item $item)
  {
    $data = $request->validate([
      'code'        => 'required|string|max:50|unique:items,code,' . $item->id,
      'name'        => 'required|string|max:255',
      'category_id' => 'required|exists:categories,id',
      'unit_id'     => 'required|exists:units,id',
      'description' => 'nullable|string|max:1000',
      'min_stock'   => 'required|numeric|min:0',
      'max_stock'   => 'nullable|numeric|min:0',
      'is_active'   => 'boolean',
    ]);

    $data['is_active'] = $request->boolean('is_active');
    $item->update($data);
    return redirect()->route('items.index')
      ->with($data['is_active'] ? 'success' : 'warning', $data['is_active'] ? 'Item berhasil diaktifkan' : 'Item berhasil dinonaktifkan');
  }

  public function destroy(Item $item)
  {
    if ($item->stocks()->where('quantity', '>', 0)->exists()) {
      return back()->with('error', 'Cannot delete item with existing stock.');
    }
    $item->delete();
    return redirect()->route('items.index')->with('success', 'Item deleted.');
  }
}
