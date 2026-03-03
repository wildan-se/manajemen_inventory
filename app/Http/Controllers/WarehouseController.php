<?php

namespace App\Http\Controllers;

use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
  public function index()
  {
    $warehouses = Warehouse::withCount('locations')->latest()->paginate(15);
    return view('warehouses.index', compact('warehouses'));
  }

  public function create()
  {
    return view('warehouses.create');
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'code'        => 'required|string|max:50|unique:warehouses,code',
      'name'        => 'required|string|max:200',
      'address'     => 'nullable|string|max:500',
      'description' => 'nullable|string|max:500',
      'is_active'   => 'boolean',
    ]);

    $data['is_active'] = $request->boolean('is_active', true);
    Warehouse::create($data);
    return redirect()->route('warehouses.index')
      ->with($data['is_active'] ? 'success' : 'warning', $data['is_active'] ? 'Gudang berhasil diaktifkan' : 'Gudang berhasil dinonaktifkan');
  }

  public function show(Warehouse $warehouse)
  {
    $warehouse->load('locations');
    $stocks = $warehouse->stocks()->with('item.unit', 'item.category', 'location')->paginate(20);
    return view('warehouses.show', compact('warehouse', 'stocks'));
  }

  public function edit(Warehouse $warehouse)
  {
    return view('warehouses.edit', compact('warehouse'));
  }

  public function update(Request $request, Warehouse $warehouse)
  {
    $data = $request->validate([
      'code'        => 'required|string|max:50|unique:warehouses,code,' . $warehouse->id,
      'name'        => 'required|string|max:200',
      'address'     => 'nullable|string|max:500',
      'description' => 'nullable|string|max:500',
      'is_active'   => 'boolean',
    ]);

    $data['is_active'] = $request->boolean('is_active');
    $warehouse->update($data);
    return redirect()->route('warehouses.index')
      ->with($data['is_active'] ? 'success' : 'warning', $data['is_active'] ? 'Gudang berhasil diaktifkan' : 'Gudang berhasil dinonaktifkan');
  }

  public function destroy(Warehouse $warehouse)
  {
    if ($warehouse->stocks()->where('quantity', '>', 0)->exists()) {
      return back()->with('error', 'Cannot delete warehouse with existing stock.');
    }
    $warehouse->delete();
    return redirect()->route('warehouses.index')->with('success', 'Warehouse deleted.');
  }
}
