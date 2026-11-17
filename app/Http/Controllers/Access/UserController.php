<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('access.users.index', [
            'roles' => Role::query()->orderBy('name')->get(),
        ]);
    }

    public function data(Request $request): JsonResponse
    {
        $draw = max(0, (int) $request->input('draw', 0));
        $start = max(0, (int) $request->input('start', 0));
        $length = min(100, max(1, (int) $request->input('length', 10)));
        $searchInput = $request->input('search.value', '');
        $search = is_string($searchInput) ? trim(mb_substr($searchInput, 0, 100)) : '';
        $roleId = filter_var($request->input('role_id'), FILTER_VALIDATE_INT) ?: null;
        $sortableColumns = [0 => 'users.name', 1 => 'users.email', 3 => 'users.is_active'];
        $orderColumn = $sortableColumns[(int) $request->input('order.0.column', 0)] ?? 'users.name';
        $orderDirection = $request->input('order.0.dir') === 'desc' ? 'desc' : 'asc';

        $query = User::query()
            ->when($search !== '', function ($query) use ($search): void {
                $query->where(function ($query) use ($search): void {
                    $query->where('users.name', 'like', "%{$search}%")
                        ->orWhere('users.email', 'like', "%{$search}%")
                        ->orWhereHas('roles', fn ($roles) => $roles->where('name', 'like', "%{$search}%"));
                });
            })
            ->when($roleId, fn ($query) => $query->whereHas(
                'roles',
                fn ($roles) => $roles->whereKey($roleId),
            ));

        $total = User::query()->count();
        $filtered = (clone $query)->count();
        $users = $query
            ->with('roles:id,name')
            ->orderBy($orderColumn, $orderDirection)
            ->orderBy('users.id')
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $users->map(fn (User $user): array => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->roles->pluck('name')->implode(', '),
                'role_ids' => $user->roles->pluck('id')->all(),
                'is_active' => $user->is_active,
            ])->all(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'integer', 'distinct', 'exists:roles,id'],
            'password' => ['required', 'string', 'min:12', 'confirmed'],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($data): void {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'is_active' => $data['is_active'],
            ]);
            $user->roles()->sync($data['roles']);
        });

        return redirect()->route('access.users.index')->with('success', 'Pengguna berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'roles' => ['required', 'array', 'min:1'],
            'roles.*' => ['required', 'integer', 'distinct', 'exists:roles,id'],
            'password' => ['nullable', 'string', 'min:12', 'confirmed'],
            'is_active' => ['required', 'boolean'],
        ]);

        DB::transaction(function () use ($request, $user, $data): void {
            $administratorRole = Role::query()->where('slug', 'administrator')->lockForUpdate()->first();
            $lockedUser = User::query()->lockForUpdate()->findOrFail($user->id);
            $newRoleIds = array_map('intval', $data['roles']);
            $currentRoleIds = $lockedUser->roles()->pluck('roles.id')->all();
            sort($newRoleIds);
            sort($currentRoleIds);

            if ($lockedUser->id === $request->user()->id
                && ($currentRoleIds !== $newRoleIds || ! $data['is_active'])) {
                throw ValidationException::withMessages([
                    'roles' => 'Anda tidak dapat mengubah peran atau menonaktifkan akun sendiri.',
                ]);
            }

            if ($administratorRole !== null
                && in_array($administratorRole->id, $currentRoleIds, true)
                && (! in_array($administratorRole->id, $newRoleIds, true) || ! $data['is_active'])
                && $administratorRole->users()->where('is_active', true)->count() <= 1) {
                throw ValidationException::withMessages([
                    'roles' => 'Administrator aktif terakhir tidak dapat dinonaktifkan.',
                ]);
            }

            $attributes = [
                'name' => $data['name'],
                'email' => $data['email'],
                'is_active' => $data['is_active'],
            ];
            if (filled($data['password'] ?? null)) {
                $attributes['password'] = $data['password'];
            }

            $lockedUser->update($attributes);
            $lockedUser->roles()->sync($newRoleIds);
        });

        return redirect()->route('access.users.index')->with('success', 'Pengguna berhasil diperbarui.');
    }
}
