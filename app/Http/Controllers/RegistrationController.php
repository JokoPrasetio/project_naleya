<?php

namespace App\Http\Controllers;

use App\Models\Patient;
use App\Models\Registration;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Illuminate\View\View;

class RegistrationController extends Controller
{
    public function index(): View
    {
        return view('registrations.index', [
            'registrations' => Registration::query()->with('patient:id,nik,no_rm,nama')->latest()->paginate(15),
        ]);
    }

    public function create(): RedirectResponse
    {
        return redirect()->route('registrations.index', ['tambah' => 1]);
    }

    public function lookupPatient(Request $request): JsonResponse
    {
        $data = $request->validate(['nik' => ['required', 'digits:16']]);
        $patient = Patient::query()->where('nik', $data['nik'])->first();

        return response()->json([
            'found' => $patient !== null,
            'patient' => $patient?->only(['nama', 'no_rm', 'email', 'no_telepon', 'tgl_lahir', 'jenis_kelamin', 'alamat']),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $registration = $this->saveRegistration($request, 'langsung');

        return redirect()->route('registrations.index')
            ->with('success', "Pendaftaran {$registration->nodaftar} berhasil disimpan.");
    }

    public function online(): View
    {
        return view('registrations.online');
    }

    public function storeOnline(Request $request): RedirectResponse
    {
        $registration = $this->saveRegistration($request, 'online');

        return redirect()->route('registrations.online')
            ->with('registration_number', $registration->nodaftar);
    }

    private function saveRegistration(Request $request, string $source): Registration
    {
        $data = $request->validate([
            'nik' => ['required', 'digits:16'],
            'nama' => ['required', 'string', 'max:255'],
            'tgl_lahir' => ['required', 'date', 'before:today'],
            'jenis_kelamin' => ['required', 'in:L,P'],
            'email' => ['nullable', 'email', 'max:255'],
            'no_telepon' => ['required', 'string', 'max:30'],
            'alamat' => ['nullable', 'string', 'max:255'],
            'tgl_kedatangan' => ['required', 'date', 'after_or_equal:today'],
            'keperluan' => ['required', 'string', 'max:500'],
            'catatan' => ['nullable', 'string', 'max:1000'],
        ]);

        return DB::transaction(function () use ($data, $request, $source): Registration {
            $patient = Patient::query()->where('nik', $data['nik'])->lockForUpdate()->first();

            if ($patient !== null) {
                if ($patient->nama !== $data['nama']
                    || $patient->tgl_lahir->toDateString() !== $data['tgl_lahir']
                    || $patient->jenis_kelamin !== $data['jenis_kelamin']) {
                    throw ValidationException::withMessages([
                        'nik' => 'Data identitas tidak dapat diverifikasi. Periksa NIK, nama, tanggal lahir, dan jenis kelamin.',
                    ]);
                }
            } else {
                $patient = Patient::create([
                    'nik' => $data['nik'],
                    'no_rm' => 'RM-'.Str::ulid(),
                    'nama' => $data['nama'],
                    'tgl_lahir' => $data['tgl_lahir'],
                    'jenis_kelamin' => $data['jenis_kelamin'],
                    'email' => $data['email'] ?? null,
                    'no_telepon' => $data['no_telepon'],
                    'alamat' => $data['alamat'] ?? null,
                ]);
            }

            return Registration::create([
                'nodaftar' => 'REG-'.Str::ulid(),
                'tgl_daftar' => now(),
                'patient_id' => $patient->id,
                'status' => 'menunggu',
                'sumber' => $source,
                'user_id' => $source === 'langsung' ? $request->user()->id : null,
                'tgl_kedatangan' => $data['tgl_kedatangan'],
                'keperluan' => $data['keperluan'],
                'catatan' => $data['catatan'] ?? null,
                'no_telepon' => $data['no_telepon'],
                'email' => $data['email'] ?? null,
            ]);
        });
    }
}
