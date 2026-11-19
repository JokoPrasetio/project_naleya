<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\TypeExamination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TypeExaminationManagementTest extends TestCase
{
    use RefreshDatabase;

    /** @param array<int, string> $permissions */
    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['permissions' => $permissions]));

        return $user;
    }

    public function test_guest_and_unpermitted_user_cannot_access_type_examinations(): void
    {
        $this->get(route('master.type_examinations.index'))->assertRedirect(route('login'));
        $this->get(route('master.type_examinations.data'))->assertRedirect(route('login'));

        $this->actingAs($this->userWithPermissions(['dashboard.view']))
            ->get(route('master.type_examinations.index'))->assertForbidden();
        $this->post(route('master.type_examinations.store'), [
            'items' => [['name' => 'Laboratorium', 'status' => '1']],
        ])->assertForbidden();
        $this->assertDatabaseCount('type_examinations', 0);
    }

    public function test_viewer_can_read_data_but_cannot_see_management_controls(): void
    {
        TypeExamination::factory()->create(['name' => 'Laboratorium']);
        $this->actingAs($this->userWithPermissions(['master.type_examinations.view']));

        $this->get(route('master.type_examinations.index'))
            ->assertOk()->assertSee('Master Jenis Pemeriksaan')->assertDontSee('Tambah Data');
        $this->getJson(route('master.type_examinations.data'))
            ->assertOk()->assertJsonPath('data.0.name', 'Laboratorium');
    }

    public function test_manager_can_open_crud_modals_and_create_multiple_boolean_statuses(): void
    {
        $this->actingAs($this->userWithPermissions(['master.type_examinations.manage']));

        $this->get(route('master.type_examinations.index'))
            ->assertOk()->assertSee('Tambah Data')
            ->assertSee('id="typeExaminationModal"', false)
            ->assertSee('id="deleteTypeExaminationModal"', false)
            ->assertSee('Tambah baris');

        $this->post(route('master.type_examinations.store'), [
            'items' => [
                ['name' => 'Laboratorium', 'status' => '1'],
                ['name' => 'Radiologi', 'status' => '0'],
            ],
        ])->assertRedirect(route('master.type_examinations.index'));

        $this->assertDatabaseHas('type_examinations', ['name' => 'Laboratorium', 'status' => true]);
        $this->assertDatabaseHas('type_examinations', ['name' => 'Radiologi', 'status' => false]);
        $this->assertDatabaseCount('type_examinations', 2);
    }

    public function test_duplicate_names_reject_the_entire_multiple_create(): void
    {
        $this->actingAs($this->userWithPermissions(['master.type_examinations.manage']));

        $this->post(route('master.type_examinations.store'), [
            'items' => [
                ['name' => 'Laboratorium', 'status' => '1'],
                ['name' => 'laboratorium', 'status' => '0'],
            ],
        ])->assertSessionHasErrors('items.1.name');

        $this->assertDatabaseCount('type_examinations', 0);
    }

    public function test_data_endpoint_filters_sorts_and_paginates_with_boolean_status(): void
    {
        TypeExamination::factory()->create(['name' => 'Laboratorium', 'status' => true]);
        TypeExamination::factory()->create(['name' => 'Radiologi', 'status' => false]);
        TypeExamination::factory()->create(['name' => 'Rehabilitasi Medik', 'status' => true]);
        $this->actingAs($this->userWithPermissions(['master.type_examinations.view']));

        $this->getJson(route('master.type_examinations.data', [
            'draw' => '<script>', 'start' => 0, 'length' => 1,
            'search' => ['value' => 'ra'],
            'order' => [['column' => 0, 'dir' => 'desc']],
        ]))->assertOk()->assertJsonPath('draw', 0)
            ->assertJsonPath('recordsTotal', 3)
            ->assertJsonPath('recordsFiltered', 2)
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.name', 'Radiologi')
            ->assertJsonPath('data.0.status', false);
    }

    public function test_manager_can_update_status_and_delete_type_examination(): void
    {
        $manager = $this->userWithPermissions(['master.type_examinations.manage']);
        $type = TypeExamination::factory()->create(['name' => 'Laboratorium', 'status' => true]);
        $this->actingAs($manager);

        $this->put(route('master.type_examinations.update', $type), [
            'editing_type_examination_id' => $type->id,
            'name' => 'Laboratorium Klinik',
            'status' => '0',
        ])->assertRedirect(route('master.type_examinations.index'));

        $this->assertDatabaseHas('type_examinations', [
            'id' => $type->id, 'name' => 'Laboratorium Klinik', 'status' => false,
        ]);

        $this->delete(route('master.type_examinations.destroy', $type))
            ->assertRedirect(route('master.type_examinations.index'));
        $this->assertModelMissing($type);
    }

    public function test_ajax_crud_returns_json_without_redirecting(): void
    {
        $this->actingAs($this->userWithPermissions(['master.type_examinations.manage']));

        $this->postJson(route('master.type_examinations.store'), [
            'items' => [['name' => 'Laboratorium', 'status' => true]],
        ])->assertCreated()->assertJsonPath('message', 'Jenis pemeriksaan berhasil ditambahkan.');
        $type = TypeExamination::query()->where('name', 'Laboratorium')->firstOrFail();

        $this->putJson(route('master.type_examinations.update', $type), [
            'editing_type_examination_id' => $type->id,
            'name' => 'Laboratorium Klinik',
            'status' => false,
        ])->assertOk()->assertJsonPath('message', 'Jenis pemeriksaan berhasil diperbarui.');

        $this->deleteJson(route('master.type_examinations.destroy', $type))
            ->assertOk()->assertJsonPath('message', 'Jenis pemeriksaan berhasil dihapus.');
        $this->assertModelMissing($type);
    }
}
