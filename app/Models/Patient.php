<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nik', 'no_rm', 'nama', 'email', 'no_telepon', 'tgl_lahir', 'jenis_kelamin', 'alamat'])]
class Patient extends Model
{
    protected function casts(): array
    {
        return ['tgl_lahir' => 'date'];
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class);
    }
}
