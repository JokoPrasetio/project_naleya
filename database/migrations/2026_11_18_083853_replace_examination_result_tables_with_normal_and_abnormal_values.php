<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('examination_normal_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->foreignId('examination_id')->constrained()->cascadeOnDelete();
            $table->string('classification')->nullable();
            $table->string('value')->nullable();
            $table->string('normal_value_type')->nullable();
            $table->string('unit')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['examination_id', 'normal_value_type']);
        });

        Schema::create('examination_abnormal_values', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('tenant_id')->nullable();
            $table->foreignId('examination_id')->constrained()->cascadeOnDelete();
            $table->string('classification')->nullable();
            $table->string('operator')->nullable();
            $table->string('type')->nullable();
            $table->string('value')->nullable();
            $table->text('conclusion')->nullable();
            $table->text('suggestion')->nullable();
            $table->text('summary')->nullable();
            $table->string('unit')->nullable();
            $table->string('reference_value')->nullable();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->index(['examination_id', 'type']);
        });

        $ranges = DB::table('examination_reference_ranges')
            ->join('examinations', 'examinations.id', '=', 'examination_reference_ranges.examination_id')
            ->select('examination_reference_ranges.*', 'examinations.unit')
            ->orderBy('examination_reference_ranges.id')
            ->get();

        foreach ($ranges as $range) {
            foreach (['minimum' => $range->min_value, 'maximum' => $range->max_value] as $type => $value) {
                if ($value === null) {
                    continue;
                }
                DB::table('examination_normal_values')->insert([
                    'examination_id' => $range->examination_id,
                    'classification' => $range->sex,
                    'value' => $value,
                    'normal_value_type' => $type,
                    'unit' => $range->unit,
                    'sort_order' => $type === 'minimum' ? 0 : 1,
                    'created_at' => $range->created_at,
                    'updated_at' => $range->updated_at,
                ]);
            }
        }

        $options = DB::table('examination_result_options')
            ->join('examinations', 'examinations.id', '=', 'examination_result_options.examination_id')
            ->select('examination_result_options.*', 'examinations.unit')
            ->orderBy('examination_result_options.sort_order')
            ->get();

        foreach ($options as $option) {
            $table = $option->is_normal ? 'examination_normal_values' : 'examination_abnormal_values';
            $attributes = [
                'examination_id' => $option->examination_id,
                'value' => $option->label,
                'unit' => $option->unit,
                'sort_order' => $option->sort_order,
                'created_at' => $option->created_at,
                'updated_at' => $option->updated_at,
            ];
            $attributes[$option->is_normal ? 'normal_value_type' : 'type'] = 'choice';
            DB::table($table)->insert($attributes);
        }

        Schema::drop('examination_result_options');
        Schema::drop('examination_reference_ranges');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::create('examination_reference_ranges', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examination_id')->constrained()->cascadeOnDelete();
            $table->string('sex', 20)->nullable();
            $table->unsignedInteger('age_min_days')->nullable();
            $table->unsignedInteger('age_max_days')->nullable();
            $table->decimal('min_value', 14, 4)->nullable();
            $table->decimal('max_value', 14, 4)->nullable();
            $table->timestamps();
        });

        Schema::create('examination_result_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('examination_id')->constrained()->cascadeOnDelete();
            $table->string('value');
            $table->string('label');
            $table->boolean('is_normal')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
            $table->unique(['examination_id', 'value']);
        });

        $normalValues = DB::table('examination_normal_values')->orderBy('sort_order')->get();
        foreach ($normalValues->whereIn('normal_value_type', ['minimum', 'maximum'])->groupBy('examination_id') as $examinationId => $values) {
            DB::table('examination_reference_ranges')->insert([
                'examination_id' => $examinationId,
                'sex' => $values->first()?->classification,
                'min_value' => $values->firstWhere('normal_value_type', 'minimum')?->value,
                'max_value' => $values->firstWhere('normal_value_type', 'maximum')?->value,
                'created_at' => $values->min('created_at'),
                'updated_at' => $values->max('updated_at'),
            ]);
        }

        $resultOptions = [];
        foreach ($normalValues->where('normal_value_type', 'choice') as $normalValue) {
            $resultOptions[$normalValue->examination_id][] = [$normalValue, true];
        }
        foreach (DB::table('examination_abnormal_values')->where('type', 'choice')->orderBy('sort_order')->get() as $abnormalValue) {
            $resultOptions[$abnormalValue->examination_id][] = [$abnormalValue, false];
        }
        foreach ($resultOptions as $examinationId => $options) {
            foreach ($options as $sortOrder => [$option, $isNormal]) {
                DB::table('examination_result_options')->insert([
                    'examination_id' => $examinationId,
                    'value' => $option->value,
                    'label' => $option->value,
                    'is_normal' => $isNormal,
                    'sort_order' => $sortOrder,
                    'created_at' => $option->created_at,
                    'updated_at' => $option->updated_at,
                ]);
            }
        }

        Schema::drop('examination_abnormal_values');
        Schema::drop('examination_normal_values');
    }
};
