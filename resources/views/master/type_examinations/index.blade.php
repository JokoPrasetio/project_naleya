@extends('partials.layout')

@section('page-title', 'Master Jenis Pemeriksaan')

@section('content')
  <div class="page-heading">
    <div>
      <h1 class="access-page-title">Master Jenis Pemeriksaan</h1>
      <p>Kelola kelompok pemeriksaan dan status penggunaannya.</p>
    </div>
    @can('master.type_examinations.manage')
      <button class="btn btn-naleya" id="addTypeExaminationButton" type="button">
        <i class="bi bi-plus-lg me-1"></i> Tambah Data
      </button>
    @endcan
  </div>

  <section class="section-card table-card">
    <div class="table-responsive access-datatable-wrap">
      <table class="table table-modern align-middle w-100" id="typeExaminationsTable"
        data-source="{{ route('master.type_examinations.data') }}"
        data-can-manage="{{ auth()->user()->can('master.type_examinations.manage') ? '1' : '0' }}">
        <thead><tr><th>Nama</th><th>Status</th><th class="text-end">Aksi</th></tr></thead>
        <tbody></tbody>
      </table>
    </div>
    <noscript><p class="p-3 text-muted">Aktifkan JavaScript untuk menampilkan tabel jenis pemeriksaan.</p></noscript>
  </section>

  @can('master.type_examinations.manage')
    <div class="modal fade" id="typeExaminationModal" tabindex="-1" aria-labelledby="typeExaminationModalTitle" aria-hidden="true"
      data-has-errors="{{ $errors->any() ? '1' : '0' }}" data-editing-id="{{ old('editing_type_examination_id') }}"
      data-bs-backdrop="static" data-bs-keyboard="false">
      <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable">
        <div class="modal-content access-modal">
          <form id="typeExaminationForm" method="POST" action="{{ route('master.type_examinations.store') }}"
            data-no-page-loading
            data-store-url="{{ route('master.type_examinations.store') }}"
            data-update-base="{{ route('master.type_examinations.index') }}">
            @csrf
            <input id="typeExaminationFormMethod" type="hidden" name="_method" value="PUT" disabled>
            <input id="editingTypeExaminationId" type="hidden" name="editing_type_examination_id" value="{{ old('editing_type_examination_id') }}">
            <div class="modal-header">
              <div>
                <h2 class="modal-title fs-5" id="typeExaminationModalTitle">Tambah Jenis Pemeriksaan</h2>
                <p class="text-muted small mb-0" id="typeExaminationModalDescription">Tambahkan satu atau beberapa data sekaligus.</p>
              </div>
              <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
              <div class="alert alert-danger" id="typeExaminationAjaxError" role="alert" hidden></div>
              @if ($errors->any())
                <div class="alert alert-danger" role="alert">Periksa kembali data. {{ $errors->first() }}</div>
              @endif

              <div id="createTypeExaminationFields">
                <div class="d-flex justify-content-between align-items-center gap-3 mb-3">
                  <div><strong>Daftar jenis pemeriksaan</strong><div class="text-muted small">Maksimal 50 data dalam satu simpanan.</div></div>
                  <button class="btn btn-sm btn-outline-primary" id="addTypeExaminationRow" type="button"><i class="bi bi-plus-lg me-1"></i> Tambah baris</button>
                </div>
                <div class="table-responsive border rounded">
                  <table class="table table-sm align-middle mb-0">
                    <thead class="table-light"><tr><th style="width: 3rem;">No.</th><th>Nama</th><th style="width: 10rem;">Status</th><th class="text-end" style="width: 6rem;">Aksi</th></tr></thead>
                    <tbody id="typeExaminationRows"></tbody>
                  </table>
                </div>
              </div>

              <div id="editTypeExaminationFields" hidden>
                <div class="row g-3">
                  <div class="col-md-8">
                    <label class="form-label" for="typeExaminationName">Nama</label>
                    <input class="form-control @error('name') is-invalid @enderror" id="typeExaminationName" name="name" value="{{ old('name') }}" maxlength="255" required disabled>
                    @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                  <div class="col-md-4">
                    <label class="form-label" for="typeExaminationStatus">Status</label>
                    <select class="form-select @error('status') is-invalid @enderror" id="typeExaminationStatus" name="status" disabled>
                      <option value="1" @selected(old('status', '1') === '1')>Aktif</option>
                      <option value="0" @selected(old('status') === '0')>Nonaktif</option>
                    </select>
                    @error('status')<div class="invalid-feedback">{{ $message }}</div>@enderror
                  </div>
                </div>
              </div>
              <div class="col-12">
                <label class="form-label" for="typeExaminationStatus">Status</label>
                <select class="form-select" name="status" id="typeExaminationStatus">
                  <option value="1">Aktif</option>
                  <option value="0">Nonaktif</option>
                </select>
              </div>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
              <button class="btn btn-naleya" id="saveTypeExaminationButton" type="submit">Simpan Jenis Pemeriksaan</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <div class="modal fade" id="deleteTypeExaminationModal" tabindex="-1" aria-labelledby="deleteTypeExaminationModalTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content access-modal">
          <div class="modal-header"><h2 class="modal-title fs-5" id="deleteTypeExaminationModalTitle">Hapus Jenis Pemeriksaan</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
          <div class="modal-body"><p class="mb-0">Hapus <strong id="deleteTypeExaminationName"></strong>? Data yang sudah dihapus tidak dapat dikembalikan.</p></div>
          <div class="modal-footer">
            <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
            <form id="deleteTypeExaminationForm" method="POST" action="{{ route('master.type_examinations.index') }}" data-delete-base="{{ route('master.type_examinations.index') }}" data-no-page-loading>
              @csrf
              @method('DELETE')
              <button class="btn btn-danger" type="submit">Ya, hapus</button>
            </form>
          </div>
        </div>
      </div>
    </div>
  @endcan

  <div class="toast-container position-fixed top-0 end-0 p-3 app-toast-container">
    <div class="toast app-toast" id="typeExaminationToast" role="status" aria-live="polite" aria-atomic="true" data-bs-delay="5000">
      <div class="toast-body">
        <i class="bi bi-check-circle app-toast-icon" aria-hidden="true"></i>
        <div class="flex-grow-1"><strong>Berhasil</strong><small></small></div>
        <button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Tutup notifikasi"></button>
      </div>
    </div>
  </div>
@endsection

@push('scripts')
  @can('master.type_examinations.manage')
    <script>window.typeExaminationOldItems = {{ Illuminate\Support\Js::from(old('items', [['name' => '', 'status' => '1']])) }};</script>
  @endcan
  @vite('resources/js/type-examinations.js')
@endpush
