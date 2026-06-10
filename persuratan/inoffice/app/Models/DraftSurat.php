<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DraftSurat extends Model
{
    use SoftDeletes;

    protected $table = 'draft_surat';

    protected $fillable = [
        'judul', 'template_id', 'file_docx_path', 'konten_html',
        'status', 'version', 'created_by', 'reviewed_by', 'approved_by'
    ];

    public function template(): BelongsTo
    {
        return $this->belongsTo(TemplateSurat::class, 'template_id');
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function versions(): HasMany
    {
        return $this->hasMany(DraftVersion::class, 'draft_id');
    }
}
