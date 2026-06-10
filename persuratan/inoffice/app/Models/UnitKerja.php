<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class UnitKerja extends Model
{
    protected $table = 'unit_kerja';

    protected $fillable = [
        'nama', 'kode', 'parent_id', 'level', 'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /** Relasi ke parent (atasan) */
    public function parent(): BelongsTo
    {
        return $this->belongsTo(UnitKerja::class, 'parent_id');
    }

    /** Relasi ke children (bawahan) */
    public function children(): HasMany
    {
        return $this->hasMany(UnitKerja::class, 'parent_id');
    }

    /** Semua turunan rekursif */
    public function allChildren(): HasMany
    {
        return $this->children()->with('allChildren');
    }

    /** User yang tergabung di unit ini */
    public function users(): HasMany
    {
        return $this->hasMany(User::class, 'unit_kerja_id');
    }

    /** Ambil semua ID anak-cucu untuk filter hierarki */
    public function getAllDescendantIds(): array
    {
        $ids = [$this->id];
        foreach ($this->children as $child) {
            $ids = array_merge($ids, $child->getAllDescendantIds());
        }
        return $ids;
    }
}
