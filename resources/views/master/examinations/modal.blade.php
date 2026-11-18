<div class="modal fade" id="examinationModal" tabindex="-1" aria-labelledby="examinationModalTitle" aria-hidden="true">
  <div class="modal-dialog modal-xl modal-dialog-scrollable">
    <div class="modal-content access-modal">
      <form id="examinationForm" method="POST" action="{{ route('master.examinations.store') }}" data-store-url="{{ route('master.examinations.store') }}">
        @csrf
        <input type="hidden" name="_method" value="PUT" disabled>
        <input type="hidden" name="_modal_id" value="{{ old('_modal_id') }}">
        <div class="modal-header">
          <h2 class="modal-title fs-5" id="examinationModalTitle">Tambah Master Pemeriksaan</h2>
          <button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button>
        </div>
        <div class="modal-body">
          @if ($errors->any())
            <div class="alert alert-danger" role="alert">Periksa kembali data pemeriksaan. {{ $errors->first() }}</div>
          @endif

          <ul class="nav nav-tabs mb-3" id="examinationFormTabs" role="tablist">
            <li class="nav-item" role="presentation"><button class="nav-link active" id="identity-tab" data-bs-toggle="tab" data-bs-target="#identity-pane" type="button" role="tab" aria-controls="identity-pane" aria-selected="true">Data utama</button></li>
            <li class="nav-item" role="presentation" data-field="examination"><button class="nav-link" id="result-tab" data-bs-toggle="tab" data-bs-target="#result-pane" type="button" role="tab" aria-controls="result-pane" aria-selected="false">Hasil & harga</button></li>
            <li class="nav-item" role="presentation" data-field="examination"><button class="nav-link" id="normal-tab" data-bs-toggle="tab" data-bs-target="#normal-pane" type="button" role="tab" aria-controls="normal-pane" aria-selected="false">Nilai normal</button></li>
            <li class="nav-item" role="presentation" data-field="examination"><button class="nav-link" id="abnormal-tab" data-bs-toggle="tab" data-bs-target="#abnormal-pane" type="button" role="tab" aria-controls="abnormal-pane" aria-selected="false">Nilai abnormal</button></li>
            <li class="nav-item" role="presentation" data-field="package"><button class="nav-link" id="package-tab" data-bs-toggle="tab" data-bs-target="#package-pane" type="button" role="tab" aria-controls="package-pane" aria-selected="false">Anggota paket</button></li>
            <li class="nav-item" id="bulkTabItem" role="presentation"><button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk-pane" type="button" role="tab" aria-controls="bulk-pane" aria-selected="false">Tambah banyak</button></li>
          </ul>

          <div class="tab-content" id="examinationFormTabContent">
            <div class="tab-pane fade show active" id="identity-pane" role="tabpanel" aria-labelledby="identity-tab" tabindex="0">
              <div class="row g-3">
                <div class="col-md-8">
                  <label class="form-label" for="examinationName">Nama</label>
                  <input class="form-control @error('name') is-invalid @enderror" id="examinationName" name="name" value="{{ old('name') }}" maxlength="255" required>
                  @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-4">
                  <label class="form-label" for="examinationCode">Kode</label>
                  <input class="form-control @error('code') is-invalid @enderror" id="examinationCode" name="code" value="{{ old('code') }}" maxlength="50">
                  @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="examinationNodeType">Jenis node</label>
                  <select class="form-select" id="examinationNodeType" name="node_type">
                    <option value="header" @selected(old('node_type', 'header') === 'header')>Header</option>
                    <option value="sub_header" @selected(old('node_type') === 'sub_header')>Sub Header</option>
                    <option value="examination" @selected(old('node_type') === 'examination')>Pemeriksaan</option>
                  </select>
                </div>
                <div class="col-md-6" data-field="header">
                  <label class="form-label" for="examinationHeaderType">Jenis header</label>
                  <select class="form-select" id="examinationHeaderType" name="header_type"><option value="service" @selected(old('header_type', 'service') === 'service')>Layanan</option><option value="package" @selected(old('header_type') === 'package')>Paket</option></select>
                </div>
                <div class="col-12" data-field="parent">
                  <label class="form-label" for="examinationParent">Induk</label>
                  <select class="form-select" id="examinationParent" name="parent_id">
                    <option value="">Pilih induk</option>
                    @foreach ($parents as $parent)
                      <option value="{{ $parent->id }}" @selected((string) old('parent_id') === (string) $parent->id)>{{ $parent->name }} ({{ $parent->node_type === 'sub_header' ? 'Sub Header' : ($parent->node_type === 'header' ? 'Header Layanan' : 'Pemeriksaan') }})</option>
                    @endforeach
                  </select>
                  @error('parent_id')<div class="text-danger small">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6"><label class="form-label" for="examinationSortOrder">Urutan</label><input class="form-control" id="examinationSortOrder" type="number" name="sort_order" min="0" value="{{ old('sort_order', 0) }}" required></div>
                <div class="col-md-6"><label class="form-label" for="examinationActive">Status</label><select class="form-select" id="examinationActive" name="is_active"><option value="1" @selected(old('is_active', '1') == '1')>Aktif</option><option value="0" @selected(old('is_active') == '0')>Nonaktif</option></select></div>
              </div>
            </div>

            <div class="tab-pane fade" id="result-pane" data-field="examination" role="tabpanel" aria-labelledby="result-tab" tabindex="0">
              <div class="row g-3">
                <div class="col-md-4"><label class="form-label" for="examinationResultType">Tipe hasil</label><select class="form-select" id="examinationResultType" name="result_type"><option value="number" @selected(old('result_type', 'number') === 'number')>Angka</option><option value="choice" @selected(old('result_type') === 'choice')>Pilihan</option><option value="text" @selected(old('result_type') === 'text')>Teks</option></select></div>
                <div class="col-md-4"><label class="form-label" for="examinationOrderable">Memiliki harga?</label><select class="form-select" id="examinationOrderable" name="is_orderable"><option value="1" @selected(old('is_orderable') == '1')>Ya</option><option value="0" @selected(old('is_orderable', '0') == '0')>Tidak</option></select></div>
                <div class="col-md-4" data-field="price"><label class="form-label" for="examinationPrice">Harga (Rp)</label><input class="form-control" id="examinationPrice" name="price" type="text" inputmode="numeric" data-currency value="{{ old('price') }}" placeholder="0"></div>
                <div class="col-md-6"><label class="form-label" for="examinationUnit">Satuan</label><input class="form-control" id="examinationUnit" name="unit" value="{{ old('unit') }}" maxlength="50"></div>
                <div class="col-md-6"><label class="form-label" for="examinationMethod">Metode</label><input class="form-control" id="examinationMethod" name="method" value="{{ old('method') }}" maxlength="255"></div>
              </div>
            </div>

            <div class="tab-pane fade" id="normal-pane" data-field="examination" role="tabpanel" aria-labelledby="normal-tab" tabindex="0">
              <input type="hidden" name="matrix_values_present" value="1">
              <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <div><strong>Daftar nilai normal</strong><div class="text-muted small">Tambahkan satu baris untuk setiap klasifikasi usia, jenis kelamin, atau kelompok pasien.</div></div>
                <button class="btn btn-sm btn-outline-primary" id="addNormalValue" type="button"><i class="bi bi-plus-lg me-1"></i> Tambah nilai normal</button>
              </div>
              <div class="table-responsive border rounded">
                <table class="table table-sm align-middle mb-0">
                  <thead class="table-light"><tr><th>Klasifikasi</th><th>Tipe nilai normal</th><th>Nilai</th><th>Satuan</th><th class="text-end">Aksi</th></tr></thead>
                  <tbody id="normalValuesRows"></tbody>
                </table>
              </div>
              <p class="text-muted small mt-2 mb-0" id="normalValuesEmpty">Belum ada nilai normal. Pemeriksaan tetap dapat disimpan tanpa matrix.</p>
            </div>

            <div class="tab-pane fade" id="abnormal-pane" data-field="examination" role="tabpanel" aria-labelledby="abnormal-tab" tabindex="0">
              <div class="d-flex justify-content-between align-items-center gap-2 mb-3">
                <div><strong>Daftar nilai abnormal</strong><div class="text-muted small">Atur kondisi abnormal per klasifikasi; kesimpulan, saran, dan ringkasan bersifat opsional.</div></div>
                <button class="btn btn-sm btn-outline-primary" id="addAbnormalValue" type="button"><i class="bi bi-plus-lg me-1"></i> Tambah nilai abnormal</button>
              </div>
              <div class="table-responsive border rounded">
                <table class="table table-sm align-middle mb-0" style="min-width: 90rem;">
                  <thead class="table-light"><tr><th>Klasifikasi</th><th>Operator</th><th>Tipe</th><th>Nilai</th><th>Satuan</th><th>Referensi</th><th>Kesimpulan</th><th>Saran</th><th>Ringkasan</th><th class="text-end">Aksi</th></tr></thead>
                  <tbody id="abnormalValuesRows"></tbody>
                </table>
              </div>
              <p class="text-muted small mt-2 mb-0" id="abnormalValuesEmpty">Belum ada nilai abnormal. Tambahkan sesuai klasifikasi yang dibutuhkan.</p>
            </div>

            <div class="tab-pane fade" id="package-pane" data-field="package" role="tabpanel" aria-labelledby="package-tab" tabindex="0">
              <label class="form-label">Anggota paket</label>
              <div class="border rounded p-3" style="max-height: 16rem; overflow-y: auto;">
                @forelse ($services as $service)
                  <label class="d-flex align-items-center gap-2 mb-2"><input class="form-check-input" type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, array_map('intval', old('service_ids', [])), true))><span>{{ $service->name }}</span></label>
                @empty
                  <span class="text-muted small">Buat header layanan sebelum membuat paket aktif.</span>
                @endforelse
              </div>
              @error('service_ids')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>

            <div class="tab-pane fade" id="bulk-pane" role="tabpanel" aria-labelledby="bulk-tab" tabindex="0">
              <div id="additionalExaminationsSection">
                <div class="d-flex justify-content-between align-items-center gap-2 mb-3"><div><strong>Daftar pemeriksaan tambahan</strong><div class="text-muted small">Jenis node dan induk mengikuti Data utama. Pengaturan hasil, harga, metode, status, serta Matrix dapat diatur per baris.</div></div><button class="btn btn-sm btn-outline-secondary" id="addExaminationRow" type="button"><i class="bi bi-plus-lg me-1"></i> Tambah baris</button></div>
                <div class="table-responsive border rounded">
                  <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                      <tr><th>Nama</th><th>Kode</th><th>Tipe hasil</th><th>Harga (Rp)</th><th>Metode</th><th>Satuan</th><th>Status</th><th class="text-end">Aksi</th></tr>
                    </thead>
                    <tbody id="additionalExaminations">
                      <tr data-additional-empty><td class="text-center text-muted py-4" colspan="8">Klik “Tambah baris” untuk menambahkan pemeriksaan.</td></tr>
                    </tbody>
                  </table>
                </div>
                @error('additional')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
                @foreach ($errors->get('additional.*') as $messages)
                  <div class="text-danger small">{{ $messages[0] }}</div>
                @endforeach
              </div>
            </div>
          </div>
        </div>
        <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button><button class="btn btn-naleya" type="submit">Simpan Master Pemeriksaan</button></div>
      </form>
    </div>
  </div>
</div>
