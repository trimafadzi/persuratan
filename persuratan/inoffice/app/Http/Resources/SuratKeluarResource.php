<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SuratKeluarResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                   => $this->id,
            'nomor_surat_otomatis' => $this->nomor_surat_otomatis,
            'tanggal'              => $this->tanggal?->format('Y-m-d'),
            'penerima'             => $this->penerima,
            'perihal'              => $this->perihal,
            'sifat'                => $this->sifat,
            'sifat_label'          => ucfirst($this->sifat),
            'isi'                  => $this->isi,
            'status'               => $this->status,
            'file_url'             => $this->file_path
                                        ? asset('storage/' . $this->file_path)
                                        : null,
            'file_name'            => $this->file_path
                                        ? basename($this->file_path)
                                        : null,
            'created_by'           => $this->whenLoaded('creator', fn() => $this->creator
                                        ? ['id' => $this->creator->id, 'nama' => $this->creator->nama_lengkap]
                                        : null),
            'created_at'           => $this->created_at->toISOString(),
            'updated_at'           => $this->updated_at->toISOString(),
        ];
    }
}
