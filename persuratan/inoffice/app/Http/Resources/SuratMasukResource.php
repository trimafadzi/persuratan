<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\SuratMasuk;

class SuratMasukResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $statusLabels = SuratMasuk::STATUS_LABELS ?? [
            'belum_dibaca'  => 'Belum Dibaca',
            'dibaca'        => 'Dibaca',
            'didisposisi'   => 'Didisposisi',
            'selesai'       => 'Selesai',
        ];
        $statusColors = SuratMasuk::STATUS_COLORS ?? [
            'belum_dibaca'  => 'danger',
            'dibaca'        => 'warning',
            'didisposisi'   => 'info',
            'selesai'       => 'success',
        ];

        return [
            'id'             => $this->id,
            'nomor_surat'    => $this->nomor_surat,
            'tanggal_surat'  => $this->tanggal_surat?->format('Y-m-d'),
            'tanggal_terima' => $this->tanggal_terima?->format('Y-m-d'),
            'pengirim'       => $this->pengirim,
            'perihal'        => $this->perihal,
            'sifat'          => $this->sifat,
            'sifat_label'    => ucfirst($this->sifat),
            'ringkasan'      => $this->ringkasan,
            'status'         => $this->status,
            'status_label'   => $statusLabels[$this->status] ?? $this->status,
            'status_color'   => $statusColors[$this->status] ?? 'secondary',
            'file_url'       => $this->file_path
                                    ? asset('storage/' . $this->file_path)
                                    : null,
            'file_name'      => $this->file_path
                                    ? basename($this->file_path)
                                    : null,
            'unit_kerja'     => $this->whenLoaded('unitKerja', fn() => $this->unitKerja
                                    ? ['id' => $this->unitKerja->id, 'nama' => $this->unitKerja->nama]
                                    : null),
            'created_by'     => $this->whenLoaded('creator', fn() => $this->creator
                                    ? ['id' => $this->creator->id, 'nama' => $this->creator->nama_lengkap]
                                    : null),
            'disposisi'      => $this->whenLoaded('disposisi',
                                    fn() => DisposisiResource::collection($this->disposisi)
                                ),
            'created_at'     => $this->created_at->toISOString(),
            'updated_at'     => $this->updated_at->toISOString(),
        ];
    }
}
