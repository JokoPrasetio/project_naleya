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
        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('role_id')->constrained()->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->primary(['role_id', 'user_id']);
        });

        DB::table('users')
            ->whereNotNull('role_id')
            ->select(['id', 'role_id'])
            ->orderBy('id')
            ->chunkById(500, function ($users): void {
                DB::table('role_user')->insertOrIgnore(
                    $users->map(fn ($user): array => [
                        'role_id' => $user->role_id,
                        'user_id' => $user->id,
                    ])->all(),
                );
            });

        Schema::table('users', function (Blueprint $table) {
            $table->dropConstrainedForeignId('role_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $hasMultipleRoles = DB::table('role_user')
            ->select('user_id')
            ->groupBy('user_id')
            ->havingRaw('COUNT(*) > 1')
            ->exists();

        if ($hasMultipleRoles) {
            throw new RuntimeException('Cannot roll back users with multiple roles without losing access data.');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->foreignId('role_id')->nullable()->constrained()->nullOnDelete();
        });

        DB::table('role_user')->orderBy('user_id')->chunk(500, function ($assignments): void {
            foreach ($assignments as $assignment) {
                DB::table('users')->where('id', $assignment->user_id)->update([
                    'role_id' => $assignment->role_id,
                ]);
            }
        });

        Schema::dropIfExists('role_user');
    }
};
