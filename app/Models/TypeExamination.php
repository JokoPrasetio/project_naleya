<?php

namespace App\Models;

use Database\Factories\TypeExaminationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'status'])]
class TypeExamination extends Model
{
    /** @use HasFactory<TypeExaminationFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'status' => 'boolean',
        ];
    }

    public function examinations(): HasMany
    {
        return $this->hasMany(Examination::class, 'type_examination_id');
    }
}
