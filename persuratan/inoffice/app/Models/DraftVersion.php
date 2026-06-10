<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DraftVersion extends Model
{
    protected $table = 'draft_versions';

    protected $fillable = [
        'draft_id', 'file_path', 'konten_html', 'versi_ke',
        'catatan_perubahan', 'saved_by', 'saved_at'
    ];

    protected $casts = [
        'saved_at' => 'datetime',
    ];

    public function draft(): BelongsTo
    {
        return $this->belongsTo(DraftSurat::class, 'draft_id');
    }

    public function saver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'saved_by');
    }
}
