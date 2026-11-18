<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['nodaftar', 'tgl_daftar', 'patient_id', 'status', 'sumber', 'user_id', 'tgl_kedatangan', 'keperluan', 'catatan', 'no_telepon', 'email'])]
class Registration extends Model
{
    protected function casts(): array
    {
        return ['tgl_daftar' => 'datetime', 'tgl_kedatangan' => 'date'];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(Patient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
