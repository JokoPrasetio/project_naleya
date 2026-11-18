<?php

namespace App\Models;

use Database\Factories\ExaminationResultOptionFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['value', 'label', 'is_normal', 'sort_order'])]
class ExaminationResultOption extends Model
{
    /** @use HasFactory<ExaminationResultOptionFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return ['is_normal' => 'boolean'];
    }

    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class);
    }
}
