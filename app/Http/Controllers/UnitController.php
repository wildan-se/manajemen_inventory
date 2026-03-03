<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use Illuminate\Http\Request;

class UnitController extends Controller
{
  public function index()
  {
    $units = Unit::withCount('items')->latest()->paginate(15);
    return view('units.index', compact('units'));
  }

  public function create()
  {
    return view('units.create');
  }

  public function store(Request $request)
  {
    $data = $request->validate([
      'name'         => 'required|string|max:100|unique:units,name',
      'abbreviation' => 'required|string|max:20|unique:units,abbreviation',
    ]);

    Unit::create($data);
    return redirect()->route('units.index')->with('success', 'Unit created successfully.');
  }

  public function edit(Unit $unit)
  {
    return view('units.edit', compact('unit'));
  }

  public function update(Request $request, Unit $unit)
  {
    $data = $request->validate([
      'name'         => 'required|string|max:100|unique:units,name,' . $unit->id,
      'abbreviation' => 'required|string|max:20|unique:units,abbreviation,' . $unit->id,
    ]);

    $unit->update($data);
    return redirect()->route('units.index')->with('success', 'Unit updated successfully.');
  }

  public function destroy(Unit $unit)
  {
    if ($unit->items()->exists()) {
      return back()->with('error', 'Cannot delete unit with existing items.');
    }
    $unit->delete();
    return redirect()->route('units.index')->with('success', 'Unit deleted.');
  }
}
