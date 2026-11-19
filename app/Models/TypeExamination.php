<?php

namespace App\Models;

use Database\Factories\TypeExaminationFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
