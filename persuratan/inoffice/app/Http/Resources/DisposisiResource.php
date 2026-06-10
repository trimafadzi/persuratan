<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use App\Models\Disposisi;

class DisposisiResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id'                  => $this->id,
            'isi_disposisi'       => $this->isi_disposisi,
            'status'              => $this->status,
            'status_label'        => Disposisi::STATUS_LABELS[$this->status] ?? $this->status,
            'status_color'        => Disposisi::STATUS_COLORS[$this->status] ?? 'secondary',
            'tanggal_deadline'    => $this->tanggal_deadline?->format('Y-m-d'),
            'is_overdue'          => $this->isOverdue(),
            'parent_disposisi_id' => $this->parent_disposisi_id,

            'surat_masuk' => $this->whenLoaded('suratMasuk', fn() => $this->suratMasuk ? [
                'id'          => $this->suratMasuk->id,
                'nomor_surat' => $this->suratMasuk->nomor_surat,
                'perihal'     => $this->suratMasuk->perihal,
                'pengirim'    => $this->suratMasuk->pengirim,
                'sifat'       => $this->suratMasuk->sifat,
            ] : null),

            'pemberi' => $this->whenLoaded('pemberi', fn() => $this->pemberi ? [
                'id'          => $this->pemberi->id,
                'nama_lengkap'=> $this->pemberi->nama_lengkap,
                'jabatan'     => $this->pemberi->jabatan,
                'initials'    => $this->pemberi->initials,
            ] : null),

            'penerima' => $this->whenLoaded('penerima',
                fn() => $this->penerima->map(fn($u) => [
                    'id'           => $u->id,
                    'nama_lengkap' => $u->nama_lengkap,
                    'jabatan'      => $u->jabatan,
                    'initials'     => $u->initials,
                    'is_read'      => (bool) $u->pivot->is_read,
                    'read_at'      => $u->pivot->read_at,
                ])
            ),

            'laporan' => $this->whenLoaded('laporan',
                fn() => $this->laporan->map(fn($l) => [
                    'id'               => $l->id,
                    'isi_laporan'      => $l->isi_laporan,
                    'status'           => $l->status,
                    'tanggapan'        => $l->tanggapan,
                    'status_tanggapan' => $l->status_tanggapan,
                    'pelapor'          => $l->pelapor ? [
                        'id'           => $l->pelapor->id,
                        'nama_lengkap' => $l->pelapor->nama_lengkap,
                    ] : null,
                    'file_bukti' => $l->relationLoaded('fileBukti')
                        ? $l->fileBukti->map(fn($f) => [
                            'id'       => $f->id,
                            'file_url' => asset('storage/' . $f->file_path),
                            'file_name'=> $f->file_name,
                        ])
                        : [],
                    'created_at' => $l->created_at->toISOString(),
                ])
            ),

            'children' => $this->whenLoaded('children',
                fn() => DisposisiResource::collection($this->children)
            ),

            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
        ];
    }
}
