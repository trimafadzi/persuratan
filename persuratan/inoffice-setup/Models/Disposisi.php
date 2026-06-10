<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Disposisi extends Model
{
    use SoftDeletes;

    protected $table = 'disposisi';

    protected $fillable = [
        'surat_masuk_id', 'dari_user_id', 'isi_disposisi',
        'status', 'tanggal_deadline', 'parent_disposisi_id',
    ];

    protected $casts = [
        'tanggal_deadline' => 'date',
    ];

    const STATUS_LABELS = [
        'pending'     => 'Menunggu',
        'diteruskan'  => 'Diteruskan',
        'selesai'     => 'Selesai',
        'dibatalkan'  => 'Dibatalkan',
    ];

    const STATUS_COLORS = [
        'pending'     => 'warning',
        'diteruskan'  => 'info',
        'selesai'     => 'success',
        'dibatalkan'  => 'danger',
    ];

    public function suratMasuk(): BelongsTo
    {
        return $this->belongsTo(SuratMasuk::class, 'surat_masuk_id');
    }

    public function pemberi(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dari_user_id');
    }

    public function penerima(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'disposisi_penerima', 'disposisi_id', 'user_id')
                    ->withPivot('is_read', 'read_at')
                    ->withTimestamps();
    }

    public function laporan(): HasMany
    {
        return $this->hasMany(LaporanDisposisi::class, 'disposisi_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(Disposisi::class, 'parent_disposisi_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(Disposisi::class, 'parent_disposisi_id');
    }

    public function isOverdue(): bool
    {
        return $this->tanggal_deadline
            && $this->tanggal_deadline->isPast()
            && $this->status !== 'selesai';
    }
}
