<?php

namespace Tests\Feature;

use App\Models\Examination;
use App\Models\Role;
use App\Models\TypeExamination;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExaminationCrudTest extends TestCase
{
    use RefreshDatabase;

    /** @param array<int, string> $permissions */
    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['permissions' => $permissions]));

        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'parent_id' => '0',
            'type_examination_id' => TypeExamination::factory()->create()->id,
            'name' => 'Pemeriksaan Darah',
            'node_type' => 'item',
            'barcode_name' => 'Darah',
            'active' => '1',
            'price' => '25000',
            'sort_order' => '1',
        ], $overrides);
    }

    public function test_guest_and_user_without_permission_cannot_manage_examinations(): void
    {
        $this->get(route('master.examinations.index'))->assertRedirect(route('login'));
        $this->actingAs($this->userWithPermissions(['dashboard.view']))
            ->get(route('master.examinations.index'))->assertForbidden();
        $this->post(route('master.examinations.store'), $this->payload())->assertForbidden();
        $this->assertDatabaseCount('examinations', 0);
    }

    public function test_manager_can_open_page_and_create_edit_list_and_delete_examination(): void
    {
        $this->actingAs($this->userWithPermissions(['master.examinations.manage']));
        $this->get(route('master.examinations.index'))->assertOk()->assertSee('Master Pemeriksaan')->assertSee('id="examinationModal"', false);

        $payload = $this->payload([
            'code_examination' => 'CBC',
            'normal_values' => [['classification' => 'female', 'value' => '12-16', 'operator' => '>=', 'unit' => 'g/dL']],
            'abnormal_values' => [['classification' => 'female', 'operator' => '<', 'value' => '12', 'conclusion' => 'Rendah', 'suggestion' => 'Evaluasi', 'summary' => 'Hb rendah']],
            'result_fields' => [['field_key' => 'image', 'label' => 'Foto hasil', 'field_type' => 'image', 'required' => '0', 'default_value' => null]],
        ]);
        $this->postJson(route('master.examinations.store'), $payload)->assertCreated();
        $examination = Examination::query()->where('code_examination', 'CBC')->firstOrFail();

        $this->assertDatabaseHas('normal_examinations', ['examination_id' => (string) $examination->id, 'classification' => 'female', 'value' => '12-16']);
        $this->assertDatabaseHas('abnormal_examinations', ['examination_id' => (string) $examination->id, 'conclusion' => 'Rendah']);
        $this->assertDatabaseHas('examination_result_fields', ['examination_id' => $examination->id, 'field_key' => 'image', 'field_type' => 'image']);
        $this->getJson(route('master.examinations.data'))->assertOk()->assertJsonPath('data.0.name', 'Pemeriksaan Darah');
        $this->getJson(route('master.examinations.show', $examination))->assertOk()->assertJsonPath('normal_values.0.classification', 'female');
        $this->getJson(route('master.examinations.types'))->assertOk()->assertJsonPath('0.name', $examination->type->name);
        $this->getJson(route('master.examinations.parents'))->assertOk()->assertJsonCount(0);
        $this->putJson(route('master.examinations.update', $examination), $this->payload([
            'type_examination_id' => $examination->type_examination_id,
            'name' => 'Darah Lengkap',
            'code_examination' => 'CBC',
            'normal_values' => [], 'abnormal_values' => [], 'result_fields' => [],
        ]))->assertOk();
        $this->assertDatabaseHas('examinations', ['id' => $examination->id, 'name' => 'Darah Lengkap']);
        $this->deleteJson(route('master.examinations.destroy', $examination))->assertOk();
        $this->assertModelMissing($examination);
    }

    public function test_invalid_type_and_item_parent_are_rejected(): void
    {
        $this->actingAs($this->userWithPermissions(['master.examinations.manage']));
        $payload = $this->payload(['type_examination_id' => 999999]);
        $this->postJson(route('master.examinations.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('type_examination_id');
        $type = TypeExamination::factory()->create();
        $parent = Examination::factory()->create(['type_examination_id' => $type->id, 'node_type' => 'item']);
        $payload['type_examination_id'] = $type->id;
        $payload['parent_id'] = (string) $parent->id;
        $this->postJson(route('master.examinations.store'), $payload)->assertUnprocessable()->assertJsonValidationErrors('parent_id');
        $this->assertDatabaseCount('examinations', 1);
    }

    public function test_header_can_be_selected_as_parent_for_a_child_examination(): void
    {
        $this->actingAs($this->userWithPermissions(['master.examinations.manage']));
        $type = TypeExamination::factory()->create();
        $header = Examination::factory()->create([
            'type_examination_id' => $type->id,
            'node_type' => 'header',
            'name' => 'Kimia Klinik',
        ]);

        $this->getJson(route('master.examinations.parents'))
            ->assertOk()->assertJsonPath('0.name', 'Kimia Klinik');
        $this->postJson(route('master.examinations.store'), $this->payload([
            'type_examination_id' => $type->id,
            'parent_id' => (string) $header->id,
        ]))->assertCreated();

        $this->assertDatabaseHas('examinations', ['name' => 'Pemeriksaan Darah', 'parent_id' => (string) $header->id]);
    }

    public function test_normal_and_abnormal_classifications_and_operators_must_be_from_the_options(): void
    {
        $this->actingAs($this->userWithPermissions(['master.examinations.manage']));
        $payload = $this->payload([
            'normal_values' => [['classification' => 'adult', 'value' => '12', 'operator' => 'about', 'unit' => 'g/dL']],
        ]);

        $this->postJson(route('master.examinations.store'), $payload)
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['normal_values.0.classification', 'normal_values.0.operator']);
        $this->assertDatabaseCount('examinations', 0);
    }
}
