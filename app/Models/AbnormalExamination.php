<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AbnormalExamination extends Model
{
    protected $guarded = ['id'];

    public function examination(): BelongsTo
    {
        return $this->belongsTo(Examination::class, 'examination_id');
    }
}
