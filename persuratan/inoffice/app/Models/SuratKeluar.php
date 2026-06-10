<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class SuratKeluar extends Model
{
    use SoftDeletes;

    protected $table = 'surat_keluar';

    protected $fillable = [
        'nomor_surat_otomatis', 'tanggal', 'penerima',
        'perihal', 'sifat', 'isi', 'file_path',
        'status', 'created_by',
    ];

    protected $casts = [
        'tanggal' => 'date',
    ];

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }
}
