<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'           => $this->id,
            'name'         => $this->name,
            'username'     => $this->username,
            'nama_lengkap' => $this->nama_lengkap,
            'jabatan'      => $this->jabatan,
            'email'        => $this->email,
            'initials'     => $this->initials,
            'foto_url'     => $this->foto_profil
                                ? asset('storage/' . $this->foto_profil)
                                : null,
            'unit_kerja'   => $this->whenLoaded('unitKerja', fn() => [
                'id'   => $this->unitKerja->id,
                'nama' => $this->unitKerja->nama,
                'kode' => $this->unitKerja->kode ?? null,
            ]),
            'roles'        => $this->whenLoaded('roles',
                fn() => $this->roles->pluck('slug')->toArray()
            ),
            'is_active'    => $this->is_active,
            'last_login'   => $this->last_login?->toISOString(),
        ];
    }
}
