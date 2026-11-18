@extends('partials.layout')

@section('page-title', 'Daftar Pendaftaran')

@section('content')
  <div class="page-heading">
    <div>
      <h1>Daftar Pendaftaran</h1>
      <p>Pendaftaran langsung dan online tersimpan dalam daftar yang sama.</p>
    </div>
    @can('registrations.manage')
      <button class="btn btn-naleya" type="button" data-bs-toggle="modal" data-bs-target="#registrationModal"><i class="bi bi-plus-lg me-1"></i>Pendaftaran Baru</button>
    @endcan
  </div>
  <section class="section-card table-card">
    <div class="table-responsive">
      <table class="table table-modern align-middle mb-0">
        <thead><tr><th>Nomor</th><th>Pasien</th><th>NIK</th><th>Tanggal datang</th><th>Keperluan</th><th>Sumber</th><th>Status</th></tr></thead>
        <tbody>
          @forelse($registrations as $registration)
            <tr>
              <td>{{ $registration->nodaftar }}</td>
              <td>{{ $registration->patient->nama }}<br><small class="text-muted">{{ $registration->patient->no_rm }}</small></td>
              <td>{{ $registration->patient->nik }}</td>
              <td>{{ $registration->tgl_kedatangan->format('d/m/Y') }}</td>
              <td>{{ $registration->keperluan }}</td>
              <td>{{ ucfirst($registration->sumber) }}</td>
              <td>{{ ucfirst($registration->status) }}</td>
            </tr>
          @empty
            <tr><td colspan="7" class="text-center py-4 text-muted">Belum ada pendaftaran.</td></tr>
          @endforelse
        </tbody>
      </table>
    </div>
    <div class="p-3">{{ $registrations->links() }}</div>
  </section>
  @can('registrations.manage')
    <div class="modal fade" id="registrationModal" tabindex="-1" aria-labelledby="registrationModalTitle" aria-hidden="true"
      data-bs-backdrop="static" data-bs-keyboard="false" data-open-on-load="{{ $errors->any() || old('nik') !== null || request()->boolean('tambah') ? '1' : '0' }}">
      <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
        <div class="modal-content access-modal">
          @include('registrations.form', ['online' => false])
        </div>
      </div>
    </div>
  @endcan
@endsection

@push('scripts')
  @can('registrations.manage')
    <script>
      const registrationModal = document.getElementById('registrationModal');
      if (registrationModal?.dataset.openOnLoad === '1') {
        window.bootstrap.Modal.getOrCreateInstance(registrationModal).show();
      }
    </script>
  @endcan
@endpush
