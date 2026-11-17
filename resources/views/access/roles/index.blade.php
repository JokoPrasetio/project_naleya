@extends('partials.layout')

@section('page-title', 'Peran dan Izin')

@section('content')
  <div class="page-heading">
    <div>
      <h1 class="access-page-title">Peran dan Izin</h1>
      <p>Hak akses menu dan tindakan ditetapkan melalui peran pengguna.</p>
    </div>
    @can('access.roles.manage')
      <button class="btn btn-naleya" id="addRoleButton" type="button">
        <i class="bi bi-plus-lg me-1"></i> Tambah Peran
      </button>
    @endcan
  </div>

  <div class="row g-3">
    @foreach ($roles as $role)
      <div class="col-md-6 col-xl-4">
        <article class="section-card access-role-card h-100">
          <div class="d-flex justify-content-between align-items-start gap-2">
            <div>
              <h2 class="section-title">{{ $role->name }}</h2>
              <p class="section-subtitle mb-0">{{ $role->slug }}</p>
            </div>
            @if ($role->is_system)
              <span class="badge text-bg-light">Bawaan</span>
            @endif
          </div>
          <p class="access-role-meta">{{ $role->users_count }} pengguna ·
            {{ $role->slug === 'administrator' ? 'Semua izin' : count($role->permissions ?? []).' izin' }}</p>
          @if (! $role->is_system)
            @can('access.roles.manage')
              <div class="d-flex gap-2 mt-auto">
                <button class="btn btn-sm btn-outline-secondary" type="button" data-edit-role
                  data-role-id="{{ $role->id }}" data-role-name="{{ $role->name }}"
                  data-permissions="{{ implode(',', $role->permissions ?? []) }}">Ubah izin</button>
                <button class="btn btn-sm btn-outline-danger" type="button" data-delete-role
                  data-role-name="{{ $role->name }}" data-users-count="{{ $role->users_count }}"
                  data-delete-url="{{ route('access.roles.destroy', $role) }}">Hapus</button>
              </div>
            @endcan
          @endif
        </article>
      </div>
    @endforeach
  </div>

  @can('access.roles.manage')
    @include('access.roles.form')
    <div class="modal fade" id="deleteRoleModal" tabindex="-1" aria-labelledby="deleteRoleModalTitle" aria-hidden="true">
      <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content access-modal">
          <form id="deleteRoleForm" method="POST">
            @csrf
            @method('DELETE')
            <div class="modal-header">
              <h2 class="modal-title fs-5" id="deleteRoleModalTitle">Hapus peran?</h2>
              <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
            </div>
            <div class="modal-body">
              <p class="mb-2">Peran <strong id="deleteRoleName"></strong> akan dihapus.</p>
              <p class="text-muted small mb-0" id="deleteRoleMessage">Tindakan ini tidak dapat dibatalkan.</p>
            </div>
            <div class="modal-footer">
              <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
              <button class="btn btn-danger" id="confirmDeleteRole" type="submit">Ya, hapus peran</button>
            </div>
          </form>
        </div>
      </div>
    </div>
  @endcan
@endsection

@push('scripts')
  <script src="{{ asset('scripts/pages/access-roles.js') }}?v={{ filemtime(public_path('scripts/pages/access-roles.js')) }}"></script>
@endpush
