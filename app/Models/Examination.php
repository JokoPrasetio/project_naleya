<?php

namespace App\Models;

use Database\Factories\ExaminationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['parent_id', 'code_examination', 'type_examination_id', 'name', 'node_type', 'barcode_name', 'active', 'code_machine', 'price', 'sort_order', 'method_examination', 'unit'])]
class Examination extends Model
{
    /** @use HasFactory<ExaminationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['active' => 'boolean', 'price' => 'decimal:2'];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(TypeExamination::class, 'type_examination_id');
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function children(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->orderBy('sort_order')->orderBy('name');
    }

    public function resultFields(): HasMany
    {
        return $this->hasMany(ExaminationResultFields::class)->orderBy('id');
    }

    public function normalValues(): HasMany
    {
        return $this->hasMany(NormalExamination::class, 'examination_id')->orderBy('id');
    }

    public function abnormalValues(): HasMany
    {
        return $this->hasMany(AbnormalExamination::class, 'examination_id')->orderBy('id');
    }
}
