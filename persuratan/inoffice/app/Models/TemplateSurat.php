<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TemplateSurat extends Model
{
    protected $table = 'template_surat';

    protected $fillable = [
        'nama', 'jenis', 'konten_html', 'is_active'
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function drafts(): HasMany
    {
        return $this->hasMany(DraftSurat::class, 'template_id');
    }
}
