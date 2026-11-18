<?php

namespace App\Models;

use Database\Factories\ExaminationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['parent_id', 'code', 'name', 'node_type', 'header_type', 'is_orderable', 'result_type', 'input_mode', 'unit', 'method', 'price', 'sort_order', 'is_active'])]
class Examination extends Model
{
    /** @use HasFactory<ExaminationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_orderable' => 'boolean', 'is_active' => 'boolean', 'price' => 'decimal:2'];
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id');
    }

    public function packageServices(): BelongsToMany
    {
        return $this->belongsToMany(self::class, 'package_services', 'package_id', 'service_id')
            ->withPivot('sort_order')->withTimestamps()->orderByPivot('sort_order');
    }

    public function referenceRanges(): HasMany
    {
        return $this->hasMany(ExaminationReferenceRange::class);
    }

    public function resultOptions(): HasMany
    {
        return $this->hasMany(ExaminationResultOption::class)->orderBy('sort_order');
    }
}
