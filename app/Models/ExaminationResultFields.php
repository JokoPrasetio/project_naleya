<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ExaminationResultFields extends Model
{
    protected $guarded = ['id'];

    protected function casts(): array
    {
        return ['required' => 'boolean'];
    }

    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class);
    }
}
