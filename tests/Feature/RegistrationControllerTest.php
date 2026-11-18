<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationControllerTest extends TestCase
{
    use RefreshDatabase;

    private function details(): array
    {
        return [
            'nik' => '3174010101900001',
            'nama' => 'Siti Aminah',
            'tgl_lahir' => '1990-01-01',
            'jenis_kelamin' => 'P',
            'no_telepon' => '081234567890',
            'email' => 'siti@example.test',
            'alamat' => 'Jl. Melati 1',
            'tgl_kedatangan' => today()->addDay()->toDateString(),
            'keperluan' => 'Pemeriksaan umum',
        ];
    }

    private function staff(array $permissions): User
    {
        $role = Role::factory()->create(['permissions' => $permissions]);
        $user = User::factory()->create();
        $user->roles()->attach($role);

        return $user;
    }

    public function test_online_registration_creates_patient_and_registration(): void
    {
        $this->post(route('registrations.online.store'), $this->details())
            ->assertRedirect(route('registrations.online'))
            ->assertSessionHas('registration_number');

        $this->assertDatabaseHas('patients', ['nik' => '3174010101900001', 'nama' => 'Siti Aminah']);
        $this->assertDatabaseHas('registrations', [
            'sumber' => 'online', 'status' => 'menunggu', 'user_id' => null,
            'no_telepon' => '081234567890',
        ]);
    }

    public function test_online_form_and_staff_modal_are_available_to_their_audiences(): void
    {
        $this->get(route('registrations.online'))->assertOk()->assertSee('Daftar Online');

        $this->actingAs($this->staff(['registrations.manage']))
            ->get(route('registrations.create'))->assertRedirect(route('registrations.index', ['tambah' => 1]));
        $this->get(route('registrations.index', ['tambah' => 1]))
            ->assertOk()
            ->assertSee('id="registrationModal"', false)
            ->assertSee('data-bs-backdrop="static"', false)
            ->assertSee('data-bs-keyboard="false"', false)
            ->assertSee('data-open-on-load="1"', false)
            ->assertSee('data-bs-dismiss="modal">Batal', false);
    }

    public function test_invalid_staff_registration_reopens_modal_with_old_values(): void
    {
        $this->actingAs($this->staff(['registrations.manage']))
            ->from(route('registrations.index'))
            ->post(route('registrations.store'), [...$this->details(), 'nik' => '123'])
            ->assertRedirect(route('registrations.index'))
            ->assertSessionHasErrors('nik');

        $this->get(route('registrations.index'))
            ->assertOk()
            ->assertSee('data-open-on-load="1"', false)
            ->assertSee('value="123"', false);
    }

    public function test_existing_nik_reuses_patient_for_a_second_registration(): void
    {
        $this->post(route('registrations.online.store'), $this->details());

        $this->post(route('registrations.online.store'), [
            ...$this->details(),
            'tgl_kedatangan' => today()->addDays(2)->toDateString(),
        ])->assertRedirect(route('registrations.online'));

        $this->assertDatabaseCount('patients', 1);
        $this->assertDatabaseCount('registrations', 2);
    }

    public function test_mismatched_identity_does_not_create_registration(): void
    {
        $this->post(route('registrations.online.store'), $this->details());

        $this->post(route('registrations.online.store'), [...$this->details(), 'nama' => 'Orang Lain'])
            ->assertSessionHasErrors('nik');

        $this->assertDatabaseCount('registrations', 1);
    }

    public function test_staff_can_register_patient_and_lookup_existing_nik(): void
    {
        $staff = $this->staff(['registrations.manage']);
        $this->actingAs($staff)->post(route('registrations.store'), $this->details())
            ->assertRedirect(route('registrations.index'));

        $this->getJson(route('registrations.lookup', ['nik' => '3174010101900001']))
            ->assertOk()->assertJsonPath('found', true)->assertJsonPath('patient.nama', 'Siti Aminah');
        $this->assertDatabaseHas('registrations', ['sumber' => 'langsung', 'user_id' => $staff->id]);
    }

    public function test_unprivileged_user_cannot_access_internal_registration_or_lookup(): void
    {
        $this->get(route('registrations.index'))->assertRedirect(route('login'));

        $this->actingAs($this->staff(['dashboard.view']))
            ->get(route('registrations.create'))->assertForbidden();
        $this->getJson(route('registrations.lookup', ['nik' => '3174010101900001']))->assertForbidden();
    }

    public function test_invalid_nik_and_past_visit_date_are_rejected(): void
    {
        $this->post(route('registrations.online.store'), [
            ...$this->details(),
            'nik' => '123',
            'tgl_kedatangan' => today()->subDay()->toDateString(),
        ])->assertSessionHasErrors(['nik', 'tgl_kedatangan']);

        $this->assertDatabaseCount('patients', 0);
        $this->assertDatabaseCount('registrations', 0);
    }
}
