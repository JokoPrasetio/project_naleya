<div class="modal fade" id="userModal" tabindex="-1" aria-labelledby="userModalTitle" aria-hidden="true"
  data-has-errors="{{ $errors->any() ? '1' : '0' }}" data-editing-id="{{ old('editing_user_id') }}" data-bs-backdrop="static" data-bs-keyboard="false">
  <div class="modal-dialog modal-dialog-centered modal-lg modal-dialog-scrollable">
    <div class="modal-content access-modal">
      <form id="userForm" method="POST" action="{{ route('master.users.store') }}"
        data-store-url="{{ route('master.users.store') }}"
        data-update-base="{{ route('master.users.index') }}">
        @csrf
        <input id="userFormMethod" type="hidden" name="_method" value="PUT" disabled>
        <input id="editingUserId" type="hidden" name="editing_user_id" value="{{ old('editing_user_id') }}">
        <div class="modal-header">
          <div>
            <h2 class="modal-title fs-5" id="userModalTitle">Tambah Pengguna</h2>
            <p class="text-muted small mb-0">Isi data akun dan pilih satu atau beberapa peran.</p>
          </div>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          <div class="row g-3">
            <div class="col-md-6">
              <label class="form-label" for="userName">Nama lengkap</label>
              <input class="form-control @error('name') is-invalid @enderror" id="userName" name="name"
                value="{{ old('name') }}" required>
              @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="userEmail">Email</label>
              <input class="form-control @error('email') is-invalid @enderror" id="userEmail" name="email" type="email"
                value="{{ old('email') }}" required>
              @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-12">
              <label class="form-label" for="userRoles">Peran</label>
              <select class="form-select @error('roles') is-invalid @enderror" id="userRoles" name="roles[]" multiple
                placeholder="Pilih peran">
                @foreach ($roles as $role)
                  <option value="{{ $role->id }}" @selected(in_array((string) $role->id, is_array(old('roles')) ? array_map('strval', old('roles')) : [], true))>{{ $role->name }}</option>
                @endforeach
              </select>
              <small class="text-muted">Pengguna mendapat gabungan izin dari semua peran yang dipilih.</small>
              @if ($errors->has('roles') || $errors->has('roles.*'))
                <div class="text-danger small">{{ $errors->first('roles') ?: $errors->first('roles.*') }}</div>
              @endif
            </div>
            <div class="col-md-6">
              <label class="form-label" for="userPassword">Kata sandi</label>
              <div class="input-group">
                <input class="form-control @error('password') is-invalid @enderror" id="userPassword" name="password"
                  type="password" minlength="12" autocomplete="new-password" @required(! old('editing_user_id'))>
                <button class="btn btn-outline-secondary" type="button" data-toggle-password="userPassword"
                  aria-label="Tampilkan kata sandi" aria-pressed="false" title="Tampilkan kata sandi">
                  <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
              </div>
              <small class="text-muted" id="passwordHint">Minimal 12 karakter.</small>
              @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="userPasswordConfirmation">Ulangi kata sandi</label>
              <div class="input-group">
                <input class="form-control" id="userPasswordConfirmation" name="password_confirmation"
                  type="password" minlength="12" autocomplete="new-password" @required(! old('editing_user_id'))>
                <button class="btn btn-outline-secondary" type="button" data-toggle-password="userPasswordConfirmation"
                  aria-label="Tampilkan ulangi kata sandi" aria-pressed="false" title="Tampilkan ulangi kata sandi">
                  <i class="bi bi-eye" aria-hidden="true"></i>
                </button>
              </div>
              <div class="text-danger small mt-1 d-none" id="passwordMismatch" role="alert" aria-live="polite">
                Kata sandi dan konfirmasi tidak sama.
              </div>
            </div>
            <div class="col-12">
              <input type="hidden" name="is_active" value="0">
              <div class="form-check form-switch">
                <input class="form-check-input" id="userIsActive" name="is_active" type="checkbox" value="1"
                  @checked(old('is_active', '1'))>
                <label class="form-check-label" for="userIsActive">Akun aktif</label>
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer">
          <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button>
          <button class="btn btn-naleya" id="saveUserButton" type="submit">Simpan Pengguna</button>
        </div>
      </form>
    </div>
  </div>
</div>
