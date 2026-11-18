@extends('partials.layout')

@section('page-title', $examination ? 'Ubah Master Pemeriksaan' : 'Tambah Master Pemeriksaan')

@section('content')
  @php
    $nodeType = old('node_type', $examination?->node_type ?? 'header');
    $headerType = old('header_type', $examination?->header_type ?? 'service');
    $resultType = old('result_type', $examination?->result_type ?? 'number');
    $isOrderable = (string) old('is_orderable', (int) ($examination?->is_orderable ?? false));
    $isActive = (string) old('is_active', (int) ($examination?->is_active ?? true));
    $selectedServices = array_map('intval', old('service_ids', $examination?->packageServices->pluck('id')->all() ?? []));
    $normalValues = old('normal_values', $examination?->normalValues->map(fn ($value) => [
      'classification' => $value->classification ?? 'Umum',
      'normal_value_type' => $value->normal_value_type,
      'value' => $value->value,
      'unit' => $value->unit,
    ])->values()->all() ?? []);
    $abnormalValues = old('abnormal_values', $examination?->abnormalValues->map(fn ($value) => [
      'classification' => $value->classification ?? 'Umum',
      'operator' => $value->operator,
      'type' => $value->type,
      'value' => $value->value,
      'unit' => $value->unit,
      'reference_value' => $value->reference_value,
      'conclusion' => $value->conclusion,
      'suggestion' => $value->suggestion,
      'summary' => $value->summary,
    ])->values()->all() ?? []);
  @endphp

  <div class="page-heading">
    <div>
      <h1 class="access-page-title">{{ $examination ? 'Ubah' : 'Tambah' }} Master Pemeriksaan</h1>
      <p>Lengkapi data secara bertahap. Nilai normal dan abnormal dapat dibuat untuk banyak klasifikasi usia atau jenis kelamin.</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('master.examinations.index') }}"><i class="bi bi-arrow-left me-1"></i> Kembali ke daftar</a>
  </div>

  <form id="examinationPageForm" method="POST" action="{{ $examination ? route('master.examinations.update', $examination) : route('master.examinations.store') }}">
    @csrf
    @if ($examination) @method('PUT') @endif

    <section class="section-card p-0 overflow-hidden">
      @if ($errors->any())
        <div class="alert alert-danger rounded-0 border-0 border-bottom mb-0" role="alert">
          <strong>Data belum dapat disimpan.</strong> {{ $errors->first() }}
        </div>
      @endif

      <div class="px-4 pt-4">
        <ul class="nav nav-tabs" id="examinationFormTabs" role="tablist">
          <li class="nav-item" role="presentation"><button class="nav-link active" id="identity-tab" data-bs-toggle="tab" data-bs-target="#identity-pane" type="button" role="tab" aria-controls="identity-pane" aria-selected="true">1. Data utama</button></li>
          <li class="nav-item" role="presentation" data-field="examination"><button class="nav-link" id="result-tab" data-bs-toggle="tab" data-bs-target="#result-pane" type="button" role="tab" aria-controls="result-pane" aria-selected="false">2. Hasil & harga</button></li>
          <li class="nav-item" role="presentation" data-field="examination"><button class="nav-link" id="normal-tab" data-bs-toggle="tab" data-bs-target="#normal-pane" type="button" role="tab" aria-controls="normal-pane" aria-selected="false">3. Nilai normal</button></li>
          <li class="nav-item" role="presentation" data-field="examination"><button class="nav-link" id="abnormal-tab" data-bs-toggle="tab" data-bs-target="#abnormal-pane" type="button" role="tab" aria-controls="abnormal-pane" aria-selected="false">4. Nilai abnormal</button></li>
          <li class="nav-item" role="presentation" data-field="package"><button class="nav-link" id="package-tab" data-bs-toggle="tab" data-bs-target="#package-pane" type="button" role="tab" aria-controls="package-pane" aria-selected="false">Anggota paket</button></li>
          @unless ($examination)
            <li class="nav-item" role="presentation"><button class="nav-link" id="bulk-tab" data-bs-toggle="tab" data-bs-target="#bulk-pane" type="button" role="tab" aria-controls="bulk-pane" aria-selected="false">Tambah banyak</button></li>
          @endunless
        </ul>
      </div>

      <div class="tab-content p-4" id="examinationFormTabContent">
        <div class="tab-pane fade show active" id="identity-pane" role="tabpanel" aria-labelledby="identity-tab" tabindex="0">
          <div class="mb-4"><h2 class="section-title mb-1">Identitas dan posisi</h2><p class="text-muted mb-0">Tentukan jenis data dan lokasinya dalam susunan master pemeriksaan.</p></div>
          <div class="row g-3">
            <div class="col-lg-8"><label class="form-label" for="name">Nama <span class="text-danger">*</span></label><input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $examination?->name) }}" maxlength="255" required>@error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-lg-4"><label class="form-label" for="code">Kode</label><input class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $examination?->code) }}" maxlength="50" placeholder="Contoh: GLU">@error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label" for="nodeType">Jenis node <span class="text-danger">*</span></label><select class="form-select @error('node_type') is-invalid @enderror" id="nodeType" name="node_type"><option value="header" @selected($nodeType === 'header')>Header</option><option value="sub_header" @selected($nodeType === 'sub_header')>Sub Header</option><option value="examination" @selected($nodeType === 'examination')>Pemeriksaan</option></select>@error('node_type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6" data-field="header"><label class="form-label" for="headerType">Jenis header</label><select class="form-select @error('header_type') is-invalid @enderror" id="headerType" name="header_type"><option value="service" @selected($headerType === 'service')>Layanan</option><option value="package" @selected($headerType === 'package')>Paket</option></select>@error('header_type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-12" data-field="parent"><label class="form-label" for="parentId">Induk <span class="text-danger">*</span></label><select class="form-select @error('parent_id') is-invalid @enderror" id="parentId" name="parent_id"><option value="">Pilih induk</option>@foreach ($parents as $parent)@if ($parent->id !== $examination?->id)<option value="{{ $parent->id }}" @selected((string) old('parent_id', $examination?->parent_id) === (string) $parent->id)>{{ $parent->name }} ({{ $parent->node_type === 'sub_header' ? 'Sub Header' : ($parent->node_type === 'header' ? 'Header Layanan' : 'Pemeriksaan') }})</option>@endif @endforeach</select><div class="form-text">Sub Header dan pemeriksaan wajib memiliki induk dari tree layanan.</div>@error('parent_id')<div class="text-danger small">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label" for="sortOrder">Urutan</label><input class="form-control @error('sort_order') is-invalid @enderror" id="sortOrder" type="number" name="sort_order" min="0" value="{{ old('sort_order', $examination?->sort_order ?? 0) }}" required>@error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label" for="isActive">Status</label><select class="form-select" id="isActive" name="is_active"><option value="1" @selected($isActive === '1')>Aktif</option><option value="0" @selected($isActive === '0')>Nonaktif</option></select></div>
          </div>
        </div>

        <div class="tab-pane fade" id="result-pane" data-field="examination" role="tabpanel" aria-labelledby="result-tab" tabindex="0">
          <div class="mb-4"><h2 class="section-title mb-1">Hasil dan harga</h2><p class="text-muted mb-0">Atur cara input hasil, metode, satuan, dan harga pemeriksaan.</p></div>
          <div class="row g-3">
            <div class="col-md-4"><label class="form-label" for="resultType">Tipe hasil</label><select class="form-select @error('result_type') is-invalid @enderror" id="resultType" name="result_type"><option value="number" @selected($resultType === 'number')>Angka</option><option value="choice" @selected($resultType === 'choice')>Pilihan</option><option value="text" @selected($resultType === 'text')>Teks</option></select>@error('result_type')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-4"><label class="form-label" for="isOrderable">Memiliki harga?</label><select class="form-select @error('is_orderable') is-invalid @enderror" id="isOrderable" name="is_orderable"><option value="1" @selected($isOrderable === '1')>Ya</option><option value="0" @selected($isOrderable === '0')>Tidak</option></select>@error('is_orderable')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-4" data-field="price"><label class="form-label" for="price">Harga (Rp)</label><input class="form-control @error('price') is-invalid @enderror" id="price" name="price" type="text" inputmode="numeric" data-currency value="{{ old('price', $examination?->price !== null ? (float) $examination->price : null) }}" placeholder="0">@error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror</div>
            <div class="col-md-6"><label class="form-label" for="unit">Satuan</label><input class="form-control" id="unit" name="unit" value="{{ old('unit', $examination?->unit) }}" maxlength="50" placeholder="Contoh: mg/dL"></div>
            <div class="col-md-6"><label class="form-label" for="method">Metode</label><input class="form-control" id="method" name="method" value="{{ old('method', $examination?->method) }}" maxlength="255" placeholder="Contoh: Enzimatik"></div>
          </div>
        </div>

        <div class="tab-pane fade" id="normal-pane" data-field="examination" role="tabpanel" aria-labelledby="normal-tab" tabindex="0">
          <input type="hidden" name="matrix_values_present" value="1">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3"><div><h2 class="section-title mb-1">Daftar nilai normal</h2><p class="text-muted mb-0">Satu baris mewakili satu kelompok usia, jenis kelamin, atau klasifikasi pasien.</p></div><button class="btn btn-sm btn-outline-primary" id="addNormalValue" type="button"><i class="bi bi-plus-lg me-1"></i> Tambah nilai normal</button></div>
          <div class="table-responsive border rounded"><table class="table table-sm align-middle mb-0"><thead class="table-light"><tr><th style="min-width: 12rem;">Klasifikasi</th><th style="min-width: 11rem;">Tipe nilai normal</th><th style="min-width: 11rem;">Nilai</th><th style="min-width: 8rem;">Satuan</th><th class="text-end">Aksi</th></tr></thead><tbody id="normalValuesRows"></tbody></table></div>
          <p class="text-muted small mt-2 mb-0" id="normalValuesEmpty">Belum ada nilai normal. Pemeriksaan tetap dapat disimpan tanpa Matrix.</p>
        </div>

        <div class="tab-pane fade" id="abnormal-pane" data-field="examination" role="tabpanel" aria-labelledby="abnormal-tab" tabindex="0">
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3"><div><h2 class="section-title mb-1">Daftar nilai abnormal</h2><p class="text-muted mb-0">Buat kondisi abnormal per klasifikasi. Kesimpulan, saran, dan ringkasan bersifat opsional.</p></div><button class="btn btn-sm btn-outline-primary" id="addAbnormalValue" type="button"><i class="bi bi-plus-lg me-1"></i> Tambah nilai abnormal</button></div>
          <div class="table-responsive border rounded"><table class="table table-sm align-middle mb-0" style="min-width: 90rem;"><thead class="table-light"><tr><th>Klasifikasi</th><th>Operator</th><th>Tipe</th><th>Nilai</th><th>Satuan</th><th>Referensi</th><th>Kesimpulan</th><th>Saran</th><th>Ringkasan</th><th class="text-end">Aksi</th></tr></thead><tbody id="abnormalValuesRows"></tbody></table></div>
          <p class="text-muted small mt-2 mb-0" id="abnormalValuesEmpty">Belum ada nilai abnormal.</p>
        </div>

        <div class="tab-pane fade" id="package-pane" data-field="package" role="tabpanel" aria-labelledby="package-tab" tabindex="0">
          <div class="mb-3"><h2 class="section-title mb-1">Anggota paket</h2><p class="text-muted mb-0">Pilih layanan yang akan tampil sebagai anggota paket.</p></div>
          <div class="border rounded p-3" style="max-height: 22rem; overflow-y: auto;">@forelse ($services as $service)<label class="d-flex align-items-center gap-2 py-2 border-bottom"><input class="form-check-input mt-0" type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, $selectedServices, true))><span>{{ $service->name }}</span></label>@empty <span class="text-muted small">Buat header layanan sebelum membuat paket aktif.</span>@endforelse</div>
          @error('service_ids')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
        </div>

        @unless ($examination)
          <div class="tab-pane fade" id="bulk-pane" role="tabpanel" aria-labelledby="bulk-tab" tabindex="0">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-3"><div><h2 class="section-title mb-1">Tambah beberapa pemeriksaan</h2><p class="text-muted mb-0">Setiap baris mengikuti jenis node dan induk pada Data utama. Harga dan Matrix dapat berbeda.</p></div><button class="btn btn-sm btn-outline-secondary" id="addExaminationRow" type="button"><i class="bi bi-plus-lg me-1"></i> Tambah baris</button></div>
            <div class="table-responsive border rounded"><table class="table table-sm align-middle mb-0" style="min-width: 72rem;"><thead class="table-light"><tr><th>Nama</th><th>Kode</th><th>Tipe hasil</th><th>Harga (Rp)</th><th>Metode</th><th>Satuan</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody id="additionalExaminations"><tr data-additional-empty><td class="text-center text-muted py-4" colspan="8">Klik “Tambah baris” untuk menambahkan pemeriksaan.</td></tr></tbody></table></div>
            @error('additional')<div class="text-danger small mt-2">{{ $message }}</div>@enderror
            @foreach ($errors->get('additional.*') as $messages)<div class="text-danger small">{{ $messages[0] }}</div>@endforeach
          </div>
        @endunless
      </div>

      <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 border-top bg-light px-4 py-3">
        <span class="text-muted small"><span class="text-danger">*</span> Wajib diisi. Periksa setiap tab sebelum menyimpan.</span>
        <div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="{{ route('master.examinations.index') }}">Batal</a><button class="btn btn-naleya" type="submit"><i class="bi bi-check-lg me-1"></i> Simpan Master Pemeriksaan</button></div>
      </div>
    </section>
  </form>
@endsection

@push('scripts')
  <script>
    window.examinationFormInitial = {{ Illuminate\Support\Js::from([
      'normal_values' => $normalValues,
      'abnormal_values' => $abnormalValues,
      'additional' => old('additional', []),
      'error_keys' => $errors->keys(),
    ]) }};
  </script>
  <script src="{{ asset('scripts/pages/master-examination-form.js') }}?v={{ filemtime(public_path('scripts/pages/master-examination-form.js')) }}"></script>
@endpush
