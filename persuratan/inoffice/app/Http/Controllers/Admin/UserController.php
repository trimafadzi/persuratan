<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Role;
use App\Models\UnitKerja;
use App\Models\SuratMasuk;
use App\Models\Disposisi;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class UserController extends Controller
{
    private function sharedData(): array
    {
        return [
            'jumlahBelumDibaca'    => SuratMasuk::where('status', 'belum_dibaca')->count(),
            'jumlahDisposisiPending' => 0,
        ];
    }

    public function index(Request $request)
    {
        $query = User::with(['roles', 'unitKerja'])->orderBy('nama_lengkap');
        if ($request->filled('search')) {
            $query->where(fn($q) => $q->where('nama_lengkap','like',"%{$request->search}%")
                ->orWhere('username','like',"%{$request->search}%")
                ->orWhere('email','like',"%{$request->search}%"));
        }
        if ($request->filled('role')) {
            $query->whereHas('roles', fn($q) => $q->where('slug', $request->role));
        }
        if ($request->filled('status')) {
            $query->where('is_active', $request->status === 'aktif');
        }

        $users    = $query->paginate(25)->withQueryString();
        $roles    = Role::all();
        $unitList = UnitKerja::where('is_active', true)->orderBy('nama')->get();

        return view('admin.users.index', array_merge(compact('users','roles','unitList'), $this->sharedData()));
    }

    public function create()
    {
        $roles    = Role::all();
        $unitList = UnitKerja::where('is_active', true)->orderBy('nama')->get();
        return view('admin.users.create', array_merge(compact('roles','unitList'), $this->sharedData()));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:200',
            'username'     => 'required|string|unique:users,username|max:50|regex:/^[a-zA-Z0-9_.-]+$/',
            'email'        => 'required|email|unique:users,email',
            'password'     => 'required|string|min:8|confirmed',
            'jabatan'      => 'nullable|string|max:200',
            'unit_kerja_id'=> 'nullable|exists:unit_kerja,id',
            'role_ids'     => 'required|array|min:1',
            'role_ids.*'   => 'exists:roles,id',
        ], [
            'username.regex' => 'Username hanya boleh huruf, angka, titik, garis bawah, dan strip.',
        ]);

        $user = User::create([
            'name'          => $validated['nama_lengkap'],
            'nama_lengkap'  => $validated['nama_lengkap'],
            'username'      => $validated['username'],
            'email'         => $validated['email'],
            'password'      => Hash::make($validated['password']),
            'jabatan'       => $validated['jabatan'] ?? null,
            'unit_kerja_id' => $validated['unit_kerja_id'] ?? null,
            'is_active'     => true,
        ]);

        $user->roles()->sync($validated['role_ids']);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->username} berhasil ditambahkan.");
    }

    public function show(User $user)
    {
        $user->load(['roles','unitKerja']);
        return view('admin.users.show', array_merge(compact('user'), $this->sharedData()));
    }

    public function edit(User $user)
    {
        $roles    = Role::all();
        $unitList = UnitKerja::where('is_active', true)->orderBy('nama')->get();
        return view('admin.users.edit', array_merge(compact('user','roles','unitList'), $this->sharedData()));
    }

    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'nama_lengkap' => 'required|string|max:200',
            'username'     => "required|string|unique:users,username,{$user->id}|max:50|regex:/^[a-zA-Z0-9_.-]+$/",
            'email'        => "required|email|unique:users,email,{$user->id}",
            'password'     => 'nullable|string|min:8|confirmed',
            'jabatan'      => 'nullable|string|max:200',
            'unit_kerja_id'=> 'nullable|exists:unit_kerja,id',
            'role_ids'     => 'required|array|min:1',
            'role_ids.*'   => 'exists:roles,id',
            'is_active'    => 'boolean',
        ]);

        $updateData = [
            'name'          => $validated['nama_lengkap'],
            'nama_lengkap'  => $validated['nama_lengkap'],
            'username'      => $validated['username'],
            'email'         => $validated['email'],
            'jabatan'       => $validated['jabatan'] ?? null,
            'unit_kerja_id' => $validated['unit_kerja_id'] ?? null,
            'is_active'     => $request->boolean('is_active', true),
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $user->update($updateData);
        $user->roles()->sync($validated['role_ids']);

        return redirect()->route('admin.users.index')
            ->with('success', "User {$user->username} berhasil diperbarui.");
    }

    public function destroy(User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'Tidak dapat menghapus akun sendiri.');
        }
        $user->update(['is_active' => false]);
        return back()->with('success', "User {$user->username} berhasil dinonaktifkan.");
    }
}
