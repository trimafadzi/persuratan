<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class LaporanDisposisi extends Model
{
    protected $table = 'laporan_disposisi';

    protected $fillable = [
        'disposisi_id', 'dari_user_id', 'isi_laporan',
        'status', 'tanggapan', 'status_tanggapan',
        'ditanggapi_oleh', 'ditanggapi_at',
    ];

    protected $casts = [
        'ditanggapi_at' => 'datetime',
    ];

    public function disposisi(): BelongsTo
    {
        return $this->belongsTo(Disposisi::class, 'disposisi_id');
    }

    public function pelapor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'dari_user_id');
    }

    public function penanggap(): BelongsTo
    {
        return $this->belongsTo(User::class, 'ditanggapi_oleh');
    }

    public function fileBukti(): HasMany
    {
        return $this->hasMany(LaporanFileBukti::class, 'laporan_id');
    }
}
