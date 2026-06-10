<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\SuratMasuk;
use Illuminate\Http\Request;

class RoleController extends Controller
{
    private function sharedData(): array
    {
        return ['jumlahBelumDibaca' => SuratMasuk::where('status','belum_dibaca')->count(), 'jumlahDisposisiPending' => 0];
    }

    public function index()
    {
        $roles = Role::withCount('users')->orderBy('nama_role')->get();
        return view('admin.roles.index', array_merge(compact('roles'), $this->sharedData()));
    }

    public function create()
    {
        return view('admin.roles.create', $this->sharedData());
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_role' => 'required|string|max:100',
            'slug' => 'required|string|unique:roles,slug|max:100',
            'description' => 'nullable|string|max:255',
        ]);

        Role::create($validated);
        return redirect()->route('admin.roles.index')->with('success', 'Role berhasil ditambahkan.');
    }

    public function edit(Role $role)
    {
        return view('admin.roles.edit', array_merge(compact('role'), $this->sharedData()));
    }

    public function update(Request $request, Role $role)
    {
        $validated = $request->validate([
            'nama_role' => 'required|string|max:100',
            'slug' => "required|string|unique:roles,slug,{$role->id}|max:100",
            'description' => 'nullable|string|max:255',
        ]);

        $role->update($validated);
        return redirect()->route('admin.roles.index')->with('success', 'Role berhasil diperbarui.');
    }

    public function destroy(Role $role)
    {
        if ($role->users()->count() > 0) {
            return back()->with('error', 'Tidak dapat menghapus role yang masih memiliki user.');
        }
        $role->delete();
        return back()->with('success', 'Role berhasil dihapus.');
    }
}
