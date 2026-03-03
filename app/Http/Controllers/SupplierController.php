<?php

namespace App\Http\Controllers;

use App\Models\Supplier;
use Illuminate\Http\Request;

class SupplierController extends Controller
{
  public function index()
  {
    $suppliers = Supplier::latest()->paginate(15);
    return view('suppliers.index', compact('suppliers'));
  }

  public function create()
  {
    return view('suppliers.create');
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'code'           => 'required|string|max:50|unique:suppliers,code',
      'name'           => 'required|string|max:200',
      'email'          => 'nullable|email|max:200',
      'phone'          => 'nullable|string|max:30',
      'address'        => 'nullable|string|max:500',
      'contact_person' => 'nullable|string|max:200',
      'is_active'      => 'boolean',
    ]);

    $data['is_active'] = $request->boolean('is_active', true);
    Supplier::create($data);
    return redirect()->route('suppliers.index')
      ->with($data['is_active'] ? 'success' : 'warning', $data['is_active'] ? 'Supplier berhasil diaktifkan' : 'Supplier berhasil dinonaktifkan');
  }

  public function show(Supplier $supplier)
  {
    $supplier->load(['purchaseOrders' => fn($q) => $q->latest()->take(10)]);
    return view('suppliers.show', compact('supplier'));
  }

  public function edit(Supplier $supplier)
  {
    return view('suppliers.edit', compact('supplier'));
  }

  public function update(Request $request, Supplier $supplier)
  {
    $data = $request->validate([
      'code'           => 'required|string|max:50|unique:suppliers,code,' . $supplier->id,
      'name'           => 'required|string|max:200',
      'email'          => 'nullable|email|max:200',
      'phone'          => 'nullable|string|max:30',
      'address'        => 'nullable|string|max:500',
      'contact_person' => 'nullable|string|max:200',
      'is_active'      => 'boolean',
    ]);

    $data['is_active'] = $request->boolean('is_active');
    $supplier->update($data);
    return redirect()->route('suppliers.index')
      ->with($data['is_active'] ? 'success' : 'warning', $data['is_active'] ? 'Supplier berhasil diaktifkan' : 'Supplier berhasil dinonaktifkan');
  }

  public function destroy(Supplier $supplier)
  {
    if ($supplier->purchaseOrders()->exists()) {
      return back()->with('error', 'Cannot delete supplier with existing purchase orders.');
    }
    $supplier->delete();
    return redirect()->route('suppliers.index')->with('success', 'Supplier deleted.');
  }
}
