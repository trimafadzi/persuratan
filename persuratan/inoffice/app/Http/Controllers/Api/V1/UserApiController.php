<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\UserResource;
use App\Models\User;
use App\Models\UnitKerja;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Auth;

class UserApiController extends Controller
{
    /**
     * GET /api/v1/users
     * List user aktif — untuk picker penerima disposisi di mobile.
     * Mengeluarkan user yang sedang login dari daftar.
     */
    public function index(Request $request): JsonResponse
    {
        $query = User::where('is_active', true)
            ->where('id', '!=', Auth::id())
            ->with(['roles', 'unitKerja'])
            ->orderBy('nama_lengkap');

        // Filter opsional berdasarkan unit kerja
        if ($request->filled('unit_kerja_id')) {
            $query->where('unit_kerja_id', $request->unit_kerja_id);
        }

        // Search berdasarkan nama atau jabatan
        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('nama_lengkap', 'like', "%{$request->search}%")
                  ->orWhere('jabatan', 'like', "%{$request->search}%")
                  ->orWhere('username', 'like', "%{$request->search}%");
            });
        }

        $users = $query->get();

        return response()->json([
            'data' => UserResource::collection($users),
        ]);
    }

    /**
     * GET /api/v1/unit-kerja
     * List unit kerja aktif — untuk filter/dropdown di mobile.
     */
    public function unitKerja(): JsonResponse
    {
        $unitKerja = UnitKerja::where('is_active', true)
            ->orderBy('nama')
            ->get(['id', 'nama', 'kode', 'parent_id', 'level']);

        return response()->json([
            'data' => $unitKerja,
        ]);
    }
}
