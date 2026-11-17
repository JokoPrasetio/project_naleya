<div class="modal fade" id="roleModal" tabindex="-1" aria-labelledby="roleModalTitle" aria-hidden="true"
  data-has-errors="{{ $errors->any() && ! $errors->has('role') ? '1' : '0' }}" data-editing-id="{{ old('editing_role_id') }}" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content access-modal">
      <form id="roleForm" method="POST" action="{{ route('access.roles.store') }}"
        data-store-url="{{ route('access.roles.store') }}"
        data-update-base="{{ route('access.roles.index') }}">
        @csrf
        <input id="roleFormMethod" type="hidden" name="_method" value="PUT" disabled>
        <input id="editingRoleId" type="hidden" name="editing_role_id" value="{{ old('editing_role_id') }}">
        <div class="modal-header">
          <div>
            <h2 class="modal-title fs-5" id="roleModalTitle">Tambah Peran</h2>
            <p class="text-muted small mb-0">Tentukan izin menu dan tindakan untuk peran ini.</p>
          </div>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3 mb-4">
            <div class="col-md-6">
              <label class="form-label" for="roleName">Nama peran</label>
              <input class="form-control @error('name') is-invalid @enderror" id="roleName" name="name"
                value="{{ old('name') }}" required>
              @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6" id="roleSlugGroup">
              <label class="form-label" for="roleSlug">Kode peran</label>
              <input class="form-control @error('slug') is-invalid @enderror" id="roleSlug" name="slug"
                value="{{ old('slug') }}" placeholder="contoh: petugas-pendaftaran" required>
              @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
          </div>
          <h3 class="section-title mb-3">Daftar izin</h3>
          <div class="access-permission-grid">
            @foreach ($permissions as $key => $label)
              <label class="access-permission-item">
                <input class="form-check-input" type="checkbox" name="permissions[]" value="{{ $key }}"
                  @checked(in_array($key, is_array(old('permissions')) ? old('permissions') : [], true))>
                <span><strong>{{ $label }}</strong><small>{{ $key }}</small></span>
              </label>
            @endforeach
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
          <button class="btn btn-naleya" type="submit">Simpan Peran</button>
        </div>
      </form>
    </div>
  </div>
</div>
