<?php

namespace Tests\Feature;

use App\Models\Examination;
use App\Models\Role;
use App\Models\User;
use Database\Seeders\ExaminationSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExaminationManagementTest extends TestCase
{
    use RefreshDatabase;

    private function userWithPermissions(array $permissions): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::factory()->create(['permissions' => $permissions]));

        return $user;
    }

    private function payload(array $overrides = []): array
    {
        return array_replace([
            'name' => 'Kimia Klinik',
            'node_type' => 'header',
            'header_type' => 'service',
            'is_orderable' => '0',
            'sort_order' => '0',
            'is_active' => '1',
        ], $overrides);
    }

    public function test_guest_and_unpermitted_user_cannot_manage_examinations(): void
    {
        $this->get(route('master.examinations.index'))->assertRedirect(route('login'));

        $this->actingAs($this->userWithPermissions(['dashboard.view']))
            ->get(route('master.examinations.index'))->assertForbidden();
        $this->post(route('master.examinations.store'), $this->payload())->assertForbidden();
        $this->assertDatabaseCount('examinations', 0);
    }

    public function test_viewer_can_see_tree_but_cannot_change_it(): void
    {
        Examination::factory()->service()->create(['name' => 'Kimia Klinik']);
        $this->actingAs($this->userWithPermissions(['master.examinations.view']));

        $this->get(route('master.examinations.index'))->assertOk()->assertSee('Kimia Klinik')->assertDontSee('Tambah node');
        $this->post(route('master.examinations.store'), $this->payload())->assertForbidden();
    }

    public function test_user_with_only_master_permission_lands_on_master_menu_after_login(): void
    {
        $user = $this->userWithPermissions(['master.examinations.view']);

        $this->post(route('login.store'), [
            'email' => $user->email, 'password' => 'password',
        ])->assertRedirect(route('master.examinations.index'));

        $this->get(route('master.examinations.index'))->assertOk()->assertSee('Pemeriksaan');
    }

    public function test_service_and_package_show_the_same_descendants_with_computed_price(): void
    {
        $service = Examination::factory()->service()->create(['name' => 'Kimia Klinik']);
        Examination::factory()->create(['name' => 'Glukosa', 'parent_id' => $service->id, 'price' => '30000.00']);
        Examination::factory()->create(['name' => 'Asam Urat', 'parent_id' => $service->id, 'price' => '35000.00']);
        $blood = Examination::factory()->service()->create(['name' => 'Darah Lengkap']);
        Examination::factory()->create(['name' => 'Hemoglobin', 'parent_id' => $blood->id]);
        $package = Examination::factory()->package()->create(['name' => 'Paket MCU']);
        $package->packageServices()->attach($service->id);
        $secondPackage = Examination::factory()->package()->create(['name' => 'Paket MCU Kedua']);
        $secondPackage->packageServices()->attach($service->id);

        $this->actingAs($this->userWithPermissions(['master.examinations.view']))
            ->get(route('master.examinations.index', ['type' => 'service']))
            ->assertOk()->assertSee('<table', false)->assertSee('Kimia Klinik')
            ->assertSee('Darah Lengkap')->assertSee('Glukosa')->assertSee('Hemoglobin')
            ->assertDontSee('Paket MCU');

        $response = $this->get(route('master.examinations.index', ['type' => 'package']));
        $response->assertOk()->assertSee('Paket MCU')->assertSee('Kimia Klinik')
            ->assertSee('Paket MCU Kedua')->assertSee('Glukosa')->assertSee('Rp65.000')
            ->assertSee('data-tree-parent="'.$package->id.'"', false)
            ->assertSee('data-tree-parent="'.$secondPackage->id.'"', false)
            ->assertSee('data-tree-id="'.$package->id.'-'.$service->id.'"', false)
            ->assertSee('data-tree-id="'.$secondPackage->id.'-'.$service->id.'"', false)
            ->assertDontSee('Hemoglobin');
        $this->assertDatabaseCount('examinations', 7);
    }

    public function test_manager_can_create_number_examination_and_package_without_tenant(): void
    {
        $manager = $this->userWithPermissions(['master.examinations.manage']);
        $this->actingAs($manager)->post(route('master.examinations.store'), $this->payload())
            ->assertRedirect(route('master.examinations.index'));
        $service = Examination::query()->where('name', 'Kimia Klinik')->firstOrFail();

        $this->post(route('master.examinations.store'), $this->payload([
            'name' => 'Glukosa', 'node_type' => 'examination', 'header_type' => null,
            'parent_id' => $service->id, 'is_orderable' => '1', 'result_type' => 'number',
            'price' => '30000', 'unit' => 'mg/dL', 'reference_min' => '70', 'reference_max' => '100',
        ]))->assertRedirect(route('master.examinations.index'));
        $examination = Examination::query()->where('name', 'Glukosa')->firstOrFail();

        $this->post(route('master.examinations.store'), $this->payload([
            'name' => 'Paket MCU', 'header_type' => 'package', 'service_ids' => [$service->id],
        ]))->assertRedirect(route('master.examinations.index'));

        $this->assertSame('30000.00', $examination->price);
        $this->assertDatabaseHas('examination_reference_ranges', [
            'examination_id' => $examination->id, 'min_value' => 70, 'max_value' => 100,
        ]);
        $this->assertDatabaseHas('package_services', ['service_id' => $service->id]);
        $this->assertDatabaseCount('examinations', 3);
    }

    public function test_sub_header_can_be_nested_under_service_or_sub_header_only(): void
    {
        $service = Examination::factory()->service()->create(['name' => 'Darah Lengkap']);
        $examination = Examination::factory()->create(['parent_id' => $service->id]);
        $this->actingAs($this->userWithPermissions(['master.examinations.manage']));

        $this->post(route('master.examinations.store'), $this->payload([
            'name' => 'Hematologi', 'node_type' => 'sub_header', 'header_type' => null,
            'parent_id' => $service->id,
        ]))->assertRedirect(route('master.examinations.index'));
        $subHeader = Examination::query()->where('name', 'Hematologi')->firstOrFail();

        $this->post(route('master.examinations.store'), $this->payload([
            'name' => 'Sub Bagian', 'node_type' => 'sub_header', 'header_type' => null,
            'parent_id' => $subHeader->id,
        ]))->assertRedirect(route('master.examinations.index'));
        $this->post(route('master.examinations.store'), $this->payload([
            'name' => 'Posisi Salah', 'node_type' => 'sub_header', 'header_type' => null,
            'parent_id' => $examination->id,
        ]))->assertSessionHasErrors('parent_id');
        $this->post(route('master.examinations.store'), $this->payload([
            'name' => 'Tanpa Induk', 'node_type' => 'sub_header', 'header_type' => null,
        ]))->assertSessionHasErrors('parent_id');
        $this->post(route('master.examinations.store'), $this->payload([
            'name' => 'Jenis Lama', 'header_type' => 'group',
        ]))->assertSessionHasErrors('header_type');

        $this->assertDatabaseHas('examinations', [
            'name' => 'Hematologi', 'node_type' => 'sub_header',
            'header_type' => null, 'parent_id' => $service->id,
        ]);
        $this->get(route('master.examinations.index', ['type' => 'service']))
            ->assertOk()->assertSee('Sub Header')->assertSee('Sub Bagian')
            ->assertDontSee('Posisi Salah')->assertDontSee('Jenis Lama');
    }

    public function test_manager_can_open_forms_and_update_reference_price_and_package_membership(): void
    {
        $firstService = Examination::factory()->service()->create(['name' => 'Kimia Klinik']);
        $secondService = Examination::factory()->service()->create(['name' => 'Darah Lengkap']);
        $examination = Examination::factory()->create(['name' => 'Glukosa', 'parent_id' => $firstService->id]);
        $examination->referenceRanges()->create(['min_value' => 70, 'max_value' => 100]);
        $package = Examination::factory()->package()->create(['name' => 'Paket MCU']);
        $package->packageServices()->attach($firstService->id);
        $this->actingAs($this->userWithPermissions(['master.examinations.manage']));

        $this->get(route('master.examinations.create'))->assertOk()->assertSee('Jenis header');
        $this->get(route('master.examinations.edit', $examination))->assertOk()->assertSee('Glukosa')->assertSee('reference_min');
        $this->get(route('master.examinations.edit', $package))->assertOk()->assertSee('Darah Lengkap');

        $this->put(route('master.examinations.update', $examination), $this->payload([
            'name' => 'Glukosa', 'node_type' => 'examination', 'header_type' => null,
            'parent_id' => $firstService->id, 'is_orderable' => '1', 'result_type' => 'number',
            'price' => '45000', 'reference_min' => '80', 'reference_max' => '110',
        ]))->assertRedirect(route('master.examinations.index'));
        $this->put(route('master.examinations.update', $package), $this->payload([
            'name' => 'Paket MCU', 'header_type' => 'package',
            'service_ids' => [$firstService->id, $secondService->id],
        ]))->assertRedirect(route('master.examinations.index'));

        $this->assertSame('45000.00', $examination->fresh()->price);
        $this->assertDatabaseHas('examination_reference_ranges', [
            'examination_id' => $examination->id, 'min_value' => 80, 'max_value' => 110,
        ]);
        $this->assertSame(2, $package->fresh()->packageServices()->count());
    }

    public function test_choice_options_are_saved_and_duplicate_values_are_rejected(): void
    {
        $service = Examination::factory()->service()->create();
        $this->actingAs($this->userWithPermissions(['master.examinations.manage']));
        $payload = $this->payload([
            'name' => 'Protein Urine', 'node_type' => 'examination', 'header_type' => null,
            'parent_id' => $service->id, 'is_orderable' => '1', 'price' => '15000',
            'result_type' => 'choice', 'normal_options' => 'Negatif', 'abnormal_options' => 'Positif',
        ]);

        $this->post(route('master.examinations.store'), $payload)->assertRedirect(route('master.examinations.index'));
        $examination = Examination::query()->where('name', 'Protein Urine')->firstOrFail();
        $this->assertDatabaseHas('examination_result_options', [
            'examination_id' => $examination->id, 'value' => 'Negatif', 'is_normal' => 1,
        ]);

        $this->post(route('master.examinations.store'), array_replace($payload, ['abnormal_options' => 'Negatif']))
            ->assertSessionHasErrors('normal_options');
        $this->assertDatabaseCount('examinations', 2);
    }

    public function test_tree_rejects_cycles_and_prevents_deleting_used_service(): void
    {
        $service = Examination::factory()->service()->create();
        $subHeader = Examination::factory()->subHeader()->create(['parent_id' => $service->id]);
        $package = Examination::factory()->package()->create();
        $package->packageServices()->attach($service->id);
        $this->actingAs($this->userWithPermissions(['master.examinations.manage']));

        $this->put(route('master.examinations.update', $service), $this->payload([
            'name' => $service->name, 'parent_id' => $subHeader->id,
        ]))->assertSessionHasErrors('parent_id');

        $this->delete(route('master.examinations.destroy', $service))->assertSessionHasErrors('examination');
        $this->assertModelExists($service);
    }

    public function test_orderable_child_cannot_be_added_under_orderable_examination(): void
    {
        $service = Examination::factory()->service()->create();
        $parent = Examination::factory()->create(['parent_id' => $service->id]);
        $this->actingAs($this->userWithPermissions(['master.examinations.manage']));

        $this->post(route('master.examinations.store'), $this->payload([
            'name' => 'Parameter', 'node_type' => 'examination', 'header_type' => null,
            'parent_id' => $parent->id, 'is_orderable' => '1', 'result_type' => 'number',
            'price' => '10000', 'reference_min' => '1',
        ]))->assertSessionHasErrors('is_orderable');

        $this->assertDatabaseCount('examinations', 2);
    }

    public function test_active_package_requires_a_service_with_an_orderable_examination(): void
    {
        $service = Examination::factory()->service()->create();
        $this->actingAs($this->userWithPermissions(['master.examinations.manage']));

        $this->post(route('master.examinations.store'), $this->payload([
            'name' => 'Paket MCU', 'header_type' => 'package', 'service_ids' => [$service->id],
        ]))->assertSessionHasErrors('service_ids');

        $this->assertDatabaseCount('examinations', 1);
    }

    public function test_demo_seeder_creates_reusable_service_tree_without_duplicate_on_second_run(): void
    {
        $this->seed(ExaminationSeeder::class);
        $this->seed(ExaminationSeeder::class);

        $response = $this->actingAs($this->userWithPermissions(['master.examinations.view']))
            ->get(route('master.examinations.index'));

        $response->assertOk()->assertSee('Darah Lengkap')->assertSee('Hematologi')
            ->assertSee('masterTableSearch')->assertSee('data-tree-toggle');
        $this->get(route('master.examinations.index', ['type' => 'package']))
            ->assertOk()->assertSee('Paket MCU')->assertSee('Rp65.000');
        $this->assertDatabaseCount('examinations', 9);
        $this->assertDatabaseCount('package_services', 1);
        $this->assertDatabaseCount('examination_reference_ranges', 0);
        $this->assertDatabaseHas('examinations', [
            'code' => 'DEMO-HEMATOLOGI', 'node_type' => 'sub_header', 'header_type' => null,
        ]);
    }

    public function test_number_examination_can_be_saved_before_reference_range_is_approved(): void
    {
        $service = Examination::factory()->service()->create();

        $this->actingAs($this->userWithPermissions(['master.examinations.manage']))
            ->post(route('master.examinations.store'), $this->payload([
                'name' => 'Glukosa', 'node_type' => 'examination', 'header_type' => null,
                'parent_id' => $service->id, 'is_orderable' => '1',
                'result_type' => 'number', 'price' => '30000',
            ]))->assertRedirect(route('master.examinations.index'));

        $this->assertDatabaseHas('examinations', ['name' => 'Glukosa', 'price' => 30000]);
        $this->assertDatabaseCount('examination_reference_ranges', 0);
    }
}
