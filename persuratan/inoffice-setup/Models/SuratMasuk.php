<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratMasuk extends Model
{
    use SoftDeletes;

    protected $table = 'surat_masuk';

    protected $fillable = [
        'nomor_surat', 'tanggal_surat', 'tanggal_terima', 'pengirim',
        'perihal', 'sifat', 'ringkasan', 'file_path', 'status',
        'unit_kerja_id', 'created_by',
    ];

    protected $casts = [
        'tanggal_surat'  => 'date',
        'tanggal_terima' => 'date',
    ];

    // Status badge colors
    const STATUS_COLORS = [
        'belum_dibaca' => 'danger',   // merah
        'dibaca'       => 'warning',  // kuning
        'didisposisi'  => 'primary',  // biru
        'selesai'      => 'success',  // hijau
    ];

    const STATUS_LABELS = [
        'belum_dibaca' => 'Belum Dibaca',
        'dibaca'       => 'Sudah Dibaca',
        'didisposisi'  => 'Sudah Didisposisi',
        'selesai'      => 'Selesai',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function unitKerja(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'unit_kerja_id');
    }

    public function disposisi(): HasMany
    {
        return $this->hasMany(Disposisi::class, 'surat_masuk_id');
    }

    public function linkedSurat()
    {
        return $this->belongsToMany(
            SuratMasuk::class,
            'surat_masuk_links',
            'surat_id',
            'linked_surat_id'
        );
    }

    public function getStatusColorAttribute(): string
    {
        return self::STATUS_COLORS[$this->status] ?? 'secondary';
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    // Scope untuk search
    public function scopeSearch($query, string $keyword)
    {
        return $query->where(function ($q) use ($keyword) {
            $q->where('nomor_surat', 'like', "%{$keyword}%")
              ->orWhere('pengirim', 'like', "%{$keyword}%")
              ->orWhere('perihal', 'like', "%{$keyword}%")
              ->orWhere('ringkasan', 'like', "%{$keyword}%");
        });
    }
}
