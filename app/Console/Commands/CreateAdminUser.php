<?php

namespace App\Console\Commands;

use App\Models\Role;
use App\Models\User;
use Illuminate\Console\Attributes\Description;
use Illuminate\Console\Attributes\Signature;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Validator;

#[Signature('access:make-admin {email} {--name=}')]
#[Description('Create or promote an administrator account')]
class CreateAdminUser extends Command
{
    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $email = (string) $this->argument('email');
        $name = (string) ($this->option('name') ?: $this->ask('Nama administrator'));
        $existingUser = User::query()->where('email', $email)->first();
        $password = $existingUser ? null : $this->secret('Kata sandi baru (minimal 12 karakter)');

        $validator = Validator::make(
            ['email' => $email, 'name' => $name, 'password' => $password],
            [
                'email' => ['required', 'email'],
                'name' => ['required', 'string', 'max:255'],
                'password' => $existingUser ? ['nullable'] : ['required', 'string', 'min:12'],
            ],
        );

        if ($validator->fails()) {
            $this->error($validator->errors()->first());

            return self::FAILURE;
        }

        $role = Role::firstOrCreate(
            ['slug' => 'administrator'],
            ['name' => 'Administrator', 'permissions' => [], 'is_system' => true],
        );

        if ($existingUser) {
            $existingUser->update(['name' => $name, 'is_active' => true]);
            $existingUser->roles()->syncWithoutDetaching([$role->id]);
        } else {
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'is_active' => true,
            ]);
            $user->roles()->attach($role);
        }

        $this->info('Akun administrator siap digunakan.');

        return self::SUCCESS;
    }
}
