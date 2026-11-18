<?php

namespace App\Models;

use Database\Factories\ExaminationReferenceRangeFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['sex', 'age_min_days', 'age_max_days', 'min_value', 'max_value'])]
class ExaminationReferenceRange extends Model
{
    /** @use HasFactory<ExaminationReferenceRangeFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['min_value' => 'decimal:4', 'max_value' => 'decimal:4'];
    }

    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class);
    }
}
