@extends('partials.layout')

@section('page-title', 'Manajemen Pengguna')

@section('content')
  <div class="page-heading">
    <div>
      <h1 class="access-page-title">Manajemen Pengguna</h1>
      <p>Atur akun, beberapa peran, dan status pengguna.</p>
    </div>
    @can('access.users.manage')
      <button class="btn btn-naleya" id="addUserButton" type="button">
        <i class="bi bi-person-plus me-1"></i> Tambah Pengguna
      </button>
    @endcan
  </div>

  <section class="section-card table-card">
    <div class="access-filters">
      <label class="form-label mb-0" for="roleFilter">Filter peran</label>
      <select class="form-select" id="roleFilter">
        <option value="">Semua peran</option>
        @foreach ($roles as $role)
          <option value="{{ $role->id }}">{{ $role->name }}</option>
        @endforeach
      </select>
    </div>
    <div class="table-responsive access-datatable-wrap">
      <table class="table table-modern align-middle w-100" id="usersTable"
        data-source="{{ route('access.users.data') }}"
        data-can-manage="{{ auth()->user()->can('access.users.manage') ? '1' : '0' }}">
        <thead>
          <tr>
            <th>Nama</th>
            <th>Email</th>
            <th>Peran</th>
            <th>Status</th>
            <th class="text-end">Aksi</th>
          </tr>
        </thead>
        <tbody></tbody>
      </table>
    </div>
    <noscript><p class="p-3 text-muted">Aktifkan JavaScript untuk menampilkan tabel pengguna.</p></noscript>
  </section>

  @can('access.users.manage')
    @include('access.users.form')
  @endcan
@endsection

@push('scripts')
  @vite('resources/js/access-users.js')
@endpush
