<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['tenant_id', 'classification', 'value', 'normal_value_type', 'unit', 'sort_order'])]
class ExaminationNormalValue extends Model
{
    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class);
    }
}
