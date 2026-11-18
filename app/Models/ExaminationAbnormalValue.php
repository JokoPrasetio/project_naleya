<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'classification', 'operator', 'type', 'value', 'conclusion', 'suggestion', 'summary', 'unit', 'reference_value', 'sort_order'])]
class ExaminationAbnormalValue extends Model
{
    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class);
    }
}
