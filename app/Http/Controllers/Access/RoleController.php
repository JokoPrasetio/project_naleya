<?php

namespace App\Http\Controllers\Access;

use App\Http\Controllers\Controller;
use App\Models\Role;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class RoleController extends Controller
{
    public function index(): View
    {
        return view('access.roles.index', [
            'roles' => Role::query()->withCount('users')->orderBy('name')->get(),
            'permissions' => config('access.permissions'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $request->merge(['slug' => Str::lower((string) $request->input('slug'))]);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'slug' => ['required', 'alpha_dash', 'max:100', Rule::unique('roles')],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys(config('access.permissions')))],
        ]);

        Role::create([
            'name' => $data['name'],
            'slug' => $data['slug'],
            'permissions' => array_values(array_unique($data['permissions'] ?? [])),
        ]);

        return redirect()->route('access.roles.index')->with('success', 'Peran berhasil ditambahkan.');
    }

    public function update(Request $request, Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403);

        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'permissions' => ['nullable', 'array'],
            'permissions.*' => ['string', Rule::in(array_keys(config('access.permissions')))],
        ]);

        $role->update([
            'name' => $data['name'],
            'permissions' => array_values(array_unique($data['permissions'] ?? [])),
        ]);

        return redirect()->route('access.roles.index')->with('success', 'Peran berhasil diperbarui.');
    }

    public function destroy(Role $role): RedirectResponse
    {
        abort_if($role->is_system, 403);

        if ($role->users()->exists()) {
            return back()->withErrors(['role' => 'Pindahkan pengguna ke peran lain sebelum menghapus peran ini.']);
        }

        $role->delete();

        return redirect()->route('access.roles.index')->with('success', 'Peran berhasil dihapus.');
    }
}
