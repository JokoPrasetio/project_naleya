<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccessManagementTest extends TestCase
{
    use RefreshDatabase;

    private function assigned(Role $role, array $attributes = []): User
    {
        $user = User::factory()->create($attributes);
        $user->roles()->attach($role);

        return $user;
    }

    private function admin(): User
    {
        return $this->assigned(Role::factory()->create([
            'slug' => 'administrator', 'permissions' => [], 'is_system' => true,
        ]));
    }

    public function test_guest_and_operator_cannot_read_user_data(): void
    {
        $this->get(route('master.users.data'))->assertRedirect(route('login'));
        $operator = $this->assigned(Role::factory()->create(['permissions' => ['dashboard.view']]));
        $this->actingAs($operator)->get(route('master.users.data'))->assertForbidden();
        $this->get(route('master.users.index'))
            ->assertForbidden()->assertSee('403 · Akses ditolak')->assertSee('Ganti akun');
    }

    public function test_administrator_can_open_modal_pages(): void
    {
        $this->actingAs($this->admin())->get(route('master.users.index'))
            ->assertOk()->assertSee('Tambah Pengguna')->assertSee('userModal');
        $this->get(route('access.roles.index'))
            ->assertOk()->assertSee('Tambah Peran')->assertSee('roleModal');
    }

    public function test_user_can_be_created_with_multiple_roles_and_hashed_password(): void
    {
        $actor = $this->admin();
        $first = Role::factory()->create();
        $second = Role::factory()->create();

        $this->actingAs($actor)->post(route('master.users.store'), [
            'name' => 'Petugas', 'email' => 'petugas@example.test',
            'roles' => [$first->id, $second->id],
            'password' => 'rahasia-panjang-123',
            'password_confirmation' => 'rahasia-panjang-123', 'is_active' => '1',
        ])->assertRedirect(route('master.users.index'));

        $user = User::query()->where('email', 'petugas@example.test')->firstOrFail();
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $user->roles->pluck('id')->all());
        $this->assertTrue(Hash::check('rahasia-panjang-123', $user->password));
        $this->get(route('master.users.index'))
            ->assertOk()->assertSee('feedbackToast')->assertSee('Pengguna berhasil ditambahkan.');
    }

    public function test_invalid_role_is_rejected(): void
    {
        $this->actingAs($this->admin())->post(route('master.users.store'), [
            'name' => 'Petugas', 'email' => 'petugas@example.test',
            'roles' => [999999], 'password' => 'rahasia-panjang-123',
            'password_confirmation' => 'rahasia-panjang-123', 'is_active' => '1',
        ])->assertSessionHasErrors('roles.0');
        $this->assertDatabaseMissing('users', ['email' => 'petugas@example.test']);
    }

    public function test_user_roles_can_be_updated_from_modal_form(): void
    {
        $actor = $this->admin();
        $first = Role::factory()->create();
        $second = Role::factory()->create();
        $user = $this->assigned($first);

        $this->actingAs($actor)->put(route('master.users.update', $user), [
            'name' => 'Nama Baru',
            'email' => $user->email,
            'roles' => [$first->id, $second->id],
            'is_active' => '1',
        ])->assertRedirect(route('master.users.index'));

        $this->assertSame('Nama Baru', $user->fresh()->name);
        $this->assertEqualsCanonicalizing([$first->id, $second->id], $user->fresh()->roles->pluck('id')->all());
    }

    public function test_data_endpoint_filters_sorts_and_paginates(): void
    {
        $actor = $this->admin();
        $operator = Role::factory()->create(['name' => 'Operator']);
        $cashier = Role::factory()->create(['name' => 'Kasir']);
        $this->assigned($operator, ['name' => 'Budi']);
        $this->assigned($operator, ['name' => 'Cici']);
        $this->assigned($cashier, ['name' => 'Dina']);

        $this->actingAs($actor)->getJson(route('master.users.data', [
            'draw' => '<script>', 'start' => 0, 'length' => 1,
            'role_id' => $operator->id, 'search' => ['value' => 'i'],
            'order' => [['column' => 0, 'dir' => 'desc']],
        ]))->assertOk()->assertJsonPath('draw', 0)
            ->assertJsonPath('recordsTotal', 4)
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Cici')
            ->assertJsonPath('data.0.role_ids.0', $operator->id);
    }

    public function test_multiple_roles_combine_permissions(): void
    {
        $dashboard = Role::factory()->create(['permissions' => ['dashboard.view']]);
        $users = Role::factory()->create(['permissions' => ['master.users.view']]);
        $actor = $this->assigned($dashboard);
        $actor->roles()->attach($users);

        $this->actingAs($actor)->get(route('dashboard'))->assertOk();
        $this->get(route('master.users.index'))->assertOk()->assertDontSee('Tambah Pengguna');
        $this->get(route('access.roles.index'))->assertForbidden();
    }

    public function test_self_disable_and_last_admin_removal_are_rejected(): void
    {
        $admin = $this->admin();
        $adminRole = $admin->roles->first();
        $managerRole = Role::factory()->create(['permissions' => ['master.users.manage']]);
        $manager = $this->assigned($managerRole);

        $this->actingAs($admin)->put(route('master.users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email,
            'roles' => [$adminRole->id], 'is_active' => '0',
        ])->assertSessionHasErrors('roles');

        $this->actingAs($manager)->put(route('master.users.update', $admin), [
            'name' => $admin->name, 'email' => $admin->email,
            'roles' => [$managerRole->id], 'is_active' => '0',
        ])->assertSessionHasErrors('roles');

        $this->assertTrue($admin->fresh()->is_active);
        $this->assertTrue($admin->fresh()->roles()->where('slug', 'administrator')->exists());
    }

    public function test_assigned_role_cannot_be_deleted(): void
    {
        $admin = $this->admin();
        $role = Role::factory()->create();
        $this->assigned($role);

        $this->actingAs($admin)->followingRedirects()->delete(route('access.roles.destroy', $role))
            ->assertOk()->assertSee('feedbackToast')
            ->assertSee('Pindahkan pengguna ke peran lain sebelum menghapus peran ini.');
        $this->assertModelExists($role);
    }

    public function test_unassigned_role_can_be_deleted_after_confirmation(): void
    {
        $admin = $this->admin();
        $role = Role::factory()->create();

        $this->actingAs($admin)->get(route('access.roles.index'))
            ->assertOk()->assertSee('deleteRoleModal')->assertSee('data-delete-role');

        $this->delete(route('access.roles.destroy', $role))
            ->assertRedirect(route('access.roles.index'));
        $this->assertModelMissing($role);
    }

    public function test_active_and_inactive_login(): void
    {
        $role = Role::factory()->create(['permissions' => ['dashboard.view']]);
        $inactive = $this->assigned($role, ['is_active' => false]);
        $active = $this->assigned($role);

        $this->post(route('login.store'), [
            'email' => $inactive->email, 'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->post(route('login.store'), [
            'email' => $active->email, 'password' => 'password',
        ])->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($active);
        $this->post(route('logout'))->assertRedirect(route('login'));
    }

    public function test_admin_command_attaches_administrator_role(): void
    {
        $user = User::factory()->create();
        $this->artisan('access:make-admin', [
            'email' => $user->email, '--name' => 'Admin Utama',
        ])->assertExitCode(0);
        $this->assertTrue($user->fresh()->roles()->where('slug', 'administrator')->exists());
        $this->assertSame('Admin Utama', $user->fresh()->name);
    }
}
