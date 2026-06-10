<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\UnitKerja;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;

class UnitKerjaController extends Controller
{
    private function sharedData(): array
    {
        return ['jumlahBelumDibaca' => SuratMasuk::where('status','belum_dibaca')->count(), 'jumlahDisposisiPending' => 0];
    }

    public function index()
    {
        $units = UnitKerja::with('children')->whereNull('parent_id')->orderBy('nama')->get();
        return view('admin.unit-kerja.index', array_merge(compact('units'), $this->sharedData()));
    }

    public function create()
    {
        $parents = UnitKerja::where('is_active', true)->orderBy('level')->orderBy('nama')->get();
        return view('admin.unit-kerja.create', array_merge(compact('parents'), $this->sharedData()));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:200',
            'kode'      => 'required|string|unique:unit_kerja,kode|max:30',
            'parent_id' => 'nullable|exists:unit_kerja,id',
        ]);

        $level = 1;
        if ($validated['parent_id']) {
            $parent = UnitKerja::find($validated['parent_id']);
            $level  = $parent->level + 1;
        }

        UnitKerja::create(array_merge($validated, ['level' => $level, 'is_active' => true]));
        return redirect()->route('admin.unit-kerja.index')->with('success', 'Unit kerja berhasil ditambahkan.');
    }

    public function edit(UnitKerja $unitKerja)
    {
        $parents = UnitKerja::where('is_active', true)->where('id','!=',$unitKerja->id)->orderBy('nama')->get();
        return view('admin.unit-kerja.edit', array_merge(compact('unitKerja','parents'), $this->sharedData()));
    }

    public function update(Request $request, UnitKerja $unitKerja)
    {
        $validated = $request->validate([
            'nama'      => 'required|string|max:200',
            'kode'      => "required|string|unique:unit_kerja,kode,{$unitKerja->id}|max:30",
            'parent_id' => 'nullable|exists:unit_kerja,id',
            'is_active' => 'boolean',
        ]);

        $level = 1;
        if ($validated['parent_id']) {
            $parent = UnitKerja::find($validated['parent_id']);
            $level  = $parent->level + 1;
        }

        $unitKerja->update(array_merge($validated, ['level' => $level, 'is_active' => $request->boolean('is_active', true)]));
        return redirect()->route('admin.unit-kerja.index')->with('success', 'Unit kerja berhasil diperbarui.');
    }

    public function destroy(UnitKerja $unitKerja)
    {
        $unitKerja->update(['is_active' => false]);
        return back()->with('success', 'Unit kerja dinonaktifkan.');
    }

    public function show(UnitKerja $unitKerja) { return redirect()->route('admin.unit-kerja.index'); }
}
