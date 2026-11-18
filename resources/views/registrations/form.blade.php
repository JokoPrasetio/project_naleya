<form method="POST" action="{{ $online ? route('registrations.online.store') : route('registrations.store') }}">
  @csrf
  @unless($online)
    <div class="modal-header">
      <div>
        <h2 class="modal-title fs-5" id="registrationModalTitle">Pendaftaran Baru</h2>
        <p class="text-muted small mb-0">NIK yang sudah terdaftar akan memakai rekam medis pasien yang ada.</p>
      </div>
      <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
    </div>
    <div class="modal-body">
  @endunless
  <div class="row g-3">
    <div class="col-md-6">
      <label class="form-label" for="nik">NIK</label>
      <input class="form-control @error('nik') is-invalid @enderror" id="nik" name="nik" inputmode="numeric" pattern="[0-9]{16}" maxlength="16" value="{{ old('nik') }}" required @unless($online) data-lookup-url="{{ route('registrations.lookup') }}" @endunless>
      @error('nik') <div class="invalid-feedback">{{ $message }}</div> @enderror
      @unless($online) <div class="form-text" id="patientLookupMessage" role="status">Isi 16 digit NIK untuk memeriksa data pasien.</div> @endunless
    </div>
    <div class="col-md-6">
      <label class="form-label" for="nama">Nama lengkap sesuai identitas</label>
      <input class="form-control @error('nama') is-invalid @enderror" id="nama" name="nama" value="{{ old('nama') }}" required>
      @error('nama') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
      <label class="form-label" for="tgl_lahir">Tanggal lahir</label>
      <input class="form-control @error('tgl_lahir') is-invalid @enderror" id="tgl_lahir" name="tgl_lahir" type="date" max="{{ now()->subDay()->toDateString() }}" value="{{ old('tgl_lahir') }}" required>
      @error('tgl_lahir')
      <div class="invalid-feedback">{{ $message }}</div>
      @enderror
    </div>
    <div class="col-md-6">
      <label class="form-label" for="jenis_kelamin">Jenis kelamin</label>
      <select class="form-select @error('jenis_kelamin') is-invalid @enderror" id="jenis_kelamin" name="jenis_kelamin" required>
        <option value="">Pilih jenis kelamin</option>
        <option value="L" @selected(old('jenis_kelamin') === 'L')>Laki-laki</option>
        <option value="P" @selected(old('jenis_kelamin') === 'P')>Perempuan</option>
      </select>
      @error('jenis_kelamin')
        <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-6">
      <label class="form-label" for="no_telepon">Nomor telepon / WhatsApp</label>
      <input class="form-control @error('no_telepon') is-invalid @enderror" id="no_telepon" name="no_telepon" type="tel" maxlength="30" value="{{ old('no_telepon') }}" required>
      @error('no_telepon') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
      <label class="form-label" for="email">Email <span class="text-muted">(opsional)</span></label>
      <input class="form-control @error('email') is-invalid @enderror" id="email" name="email" type="email" value="{{ old('email') }}">
      @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12">
      <label class="form-label" for="alamat">Alamat <span class="text-muted">(opsional)</span></label>
      <textarea class="form-control @error('alamat') is-invalid @enderror" id="alamat" name="alamat" rows="2" maxlength="255">{{ old('alamat') }}</textarea>
      @error('alamat') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
      <label class="form-label" for="tgl_kedatangan">Tanggal kedatangan</label>
      <input class="form-control @error('tgl_kedatangan') is-invalid @enderror" id="tgl_kedatangan" name="tgl_kedatangan" type="date" min="{{ today()->toDateString() }}" value="{{ old('tgl_kedatangan', today()->toDateString()) }}" required>
      @error('tgl_kedatangan') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-md-6">
      <label class="form-label" for="keperluan">Keperluan / layanan</label>
      <input class="form-control @error('keperluan') is-invalid @enderror" id="keperluan" name="keperluan" maxlength="500" value="{{ old('keperluan') }}" required>
      @error('keperluan') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
    <div class="col-12">
      <label class="form-label" for="catatan">Catatan <span class="text-muted">(opsional)</span></label>
      <textarea class="form-control @error('catatan') is-invalid @enderror" id="catatan" name="catatan" rows="2" maxlength="1000">{{ old('catatan') }}</textarea>
      @error('catatan') <div class="invalid-feedback">{{ $message }}</div> @enderror
    </div>
  </div>
  @unless($online) </div> @endunless
  <div class="d-flex justify-content-end gap-2 {{ $online ? 'mt-4' : 'modal-footer' }}">
    @unless($online) <button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button> @endunless
    <button class="btn btn-naleya" type="submit">Simpan Pendaftaran</button>
  </div>
</form>
@unless($online)
  <script>
    const nikInput = document.getElementById('nik');
    const lookupMessage = document.getElementById('patientLookupMessage');
    let lastLookup = '';
    nikInput.addEventListener('input', async () => {
      if (nikInput.value.length !== 16 || !/^\d{16}$/.test(nikInput.value) || lastLookup === nikInput.value) return;
      const nik = nikInput.value;
      lastLookup = nik;
      lookupMessage.textContent = 'Memeriksa NIK...';
      try {
        const response = await fetch(`${nikInput.dataset.lookupUrl}?nik=${encodeURIComponent(nik)}`, { headers: { Accept: 'application/json' } });
        if (!response.ok) throw new Error('Lookup gagal');
        const result = await response.json();
        if (nikInput.value !== nik) return;
        if (!result.found) {
          lookupMessage.textContent = 'NIK belum terdaftar. Isi data pasien baru.';
          return;
        }
        lookupMessage.textContent = `Pasien terdaftar (${result.patient.no_rm}). Data pasien diisi otomatis; pendaftaran baru akan memakai rekam medis yang sama.`;
        for (const key of ['nama', 'email', 'no_telepon', 'alamat', 'jenis_kelamin']) {
          document.getElementById(key).value = result.patient[key] || '';
        }
        document.getElementById('tgl_lahir').value = result.patient.tgl_lahir?.slice(0, 10) || '';
      } catch {
        lookupMessage.textContent = 'Pemeriksaan NIK gagal. Anda tetap dapat mengisi formulir.';
      }
    });
    nikInput.addEventListener('input', () => { if (nikInput.value !== lastLookup) lastLookup = ''; });
  </script>
@endunless
