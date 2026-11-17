<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'is_active'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Role::class);
    }

    public function hasPermission(string $permission): bool
    {
        if (! $this->is_active || ! array_key_exists($permission, config('access.permissions'))) {
            return false;
        }

        $roles = $this->roles;

        if ($roles->isEmpty()) {
            return false;
        }

        if ($roles->contains('slug', 'administrator')
            || $roles->contains(fn (Role $role): bool => in_array($permission, $role->permissions ?? [], true))) {
            return true;
        }

        return str_ends_with($permission, '.view')
            && $roles->contains(fn (Role $role): bool => in_array(
                substr($permission, 0, -5).'.manage',
                $role->permissions ?? [],
                true,
            ));
    }
}
