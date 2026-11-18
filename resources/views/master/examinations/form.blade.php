@extends('partials.layout')

@section('page-title', $examination ? 'Ubah Master Pemeriksaan' : 'Tambah Master Pemeriksaan')

@section('content')
  @php
    $nodeType = old('node_type', $examination?->node_type ?? 'header');
    $headerType = old('header_type', $examination?->header_type ?? 'service');
    $resultType = old('result_type', $examination?->result_type ?? 'number');
    $isOrderable = old('is_orderable', $examination?->is_orderable ?? false);
    $selectedServices = old('service_ids', $examination?->packageServices->pluck('id')->all() ?? []);
    $referenceMin = $examination?->normalValues->firstWhere('normal_value_type', 'minimum')?->value;
    $referenceMax = $examination?->normalValues->firstWhere('normal_value_type', 'maximum')?->value;
    $normalOptions = $examination?->normalValues->where('normal_value_type', 'choice')->pluck('value')->implode("\n") ?? '';
    $abnormalOptions = $examination?->abnormalValues->where('type', 'choice')->pluck('value')->implode("\n") ?? '';
  @endphp
  <div class="page-heading">
    <div>
      <h1 class="access-page-title">{{ $examination ? 'Ubah' : 'Tambah' }} Master Pemeriksaan</h1>
      <p>Susun Header, Sub Header, dan pemeriksaan.</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('master.examinations.index') }}">Kembali ke daftar</a>
  </div>

  <form method="POST" action="{{ $examination ? route('master.examinations.update', $examination) : route('master.examinations.store') }}">
    @csrf
    @if ($examination) @method('PUT') @endif
    <div class="row g-3">
      <div class="col-lg-7">
        <section class="section-card h-100">
          <h2 class="section-title mb-3">Identitas dan posisi</h2>
          <div class="row g-3">
            <div class="col-md-8">
              <label class="form-label" for="name">Nama</label>
              <input class="form-control @error('name') is-invalid @enderror" id="name" name="name" value="{{ old('name', $examination?->name) }}" required maxlength="255">
              @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label" for="code">Kode</label>
              <input class="form-control @error('code') is-invalid @enderror" id="code" name="code" value="{{ old('code', $examination?->code) }}" maxlength="50">
              @error('code')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="nodeType">Jenis node</label>
              <select class="form-select @error('node_type') is-invalid @enderror" id="nodeType" name="node_type">
                <option value="header" @selected($nodeType === 'header')>Header</option>
                <option value="sub_header" @selected($nodeType === 'sub_header')>Sub Header</option>
                <option value="examination" @selected($nodeType === 'examination')>Pemeriksaan</option>
              </select>
              @error('node_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6" data-field="header">
              <label class="form-label" for="headerType">Jenis header</label>
              <select class="form-select @error('header_type') is-invalid @enderror" id="headerType" name="header_type">
                <option value="service" @selected($headerType === 'service')>Layanan</option>
                <option value="package" @selected($headerType === 'package')>Paket</option>
              </select>
              @error('header_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-12" data-field="parent">
              <label class="form-label" for="parentId">Induk</label>
              <select class="form-select @error('parent_id') is-invalid @enderror" id="parentId" name="parent_id">
                <option value="">Pilih induk</option>
                @foreach ($parents as $parent)
                  @if ($parent->id !== $examination?->id)
                    <option value="{{ $parent->id }}" @selected((string) old('parent_id', $examination?->parent_id) === (string) $parent->id)>{{ $parent->name }} ({{ $parent->node_type === 'sub_header' ? 'Sub Header' : ($parent->node_type === 'header' ? 'Header Layanan' : 'Pemeriksaan') }})</option>
                  @endif
                @endforeach
              </select>
              <div class="form-text">Wajib untuk Sub Header dan pemeriksaan. Header layanan serta paket berada di tingkat utama.</div>
              @error('parent_id')<div class="text-danger small">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="sortOrder">Urutan</label>
              <input class="form-control @error('sort_order') is-invalid @enderror" id="sortOrder" type="number" name="sort_order" min="0" value="{{ old('sort_order', $examination?->sort_order ?? 0) }}" required>
              @error('sort_order')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="isActive">Status</label>
              <select class="form-select" id="isActive" name="is_active">
                <option value="1" @selected(old('is_active', $examination?->is_active ?? true))>Aktif</option>
                <option value="0" @selected(! old('is_active', $examination?->is_active ?? true))>Nonaktif</option>
              </select>
            </div>
          </div>
        </section>
      </div>
      <div class="col-lg-5">
        <section class="section-card h-100" data-field="package">
          <h2 class="section-title mb-2">Anggota paket</h2>
          <p class="text-muted small">Pilih layanan yang tampil di bawah paket. Master layanan dan harga tidak disalin.</p>
          @forelse ($services as $service)
            <label class="d-flex align-items-center gap-2 mb-2">
              <input class="form-check-input" type="checkbox" name="service_ids[]" value="{{ $service->id }}" @checked(in_array($service->id, array_map('intval', $selectedServices), true))>
              <span>{{ $service->name }}</span>
            </label>
          @empty
            <p class="text-muted small">Buat header layanan sebelum membuat paket aktif.</p>
          @endforelse
          @error('service_ids')<div class="text-danger small">{{ $message }}</div>@enderror
          @error('service_ids.*')<div class="text-danger small">{{ $message }}</div>@enderror
        </section>
      </div>
      <div class="col-12" data-field="examination">
        <section class="section-card">
          <h2 class="section-title mb-3">Hasil dan harga pemeriksaan</h2>
          <div class="row g-3">
            <div class="col-md-4">
              <label class="form-label" for="resultType">Tipe hasil</label>
              <select class="form-select @error('result_type') is-invalid @enderror" id="resultType" name="result_type">
                <option value="number" @selected($resultType === 'number')>Angka</option>
                <option value="choice" @selected($resultType === 'choice')>Pilihan</option>
                <option value="text" @selected($resultType === 'text')>Teks</option>
              </select>
              @error('result_type')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4">
              <label class="form-label" for="isOrderable">Memiliki harga?</label>
              <select class="form-select @error('is_orderable') is-invalid @enderror" id="isOrderable" name="is_orderable">
                <option value="1" @selected($isOrderable)>Ya</option>
                <option value="0" @selected(! $isOrderable)>Tidak</option>
              </select>
              @error('is_orderable')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-4" data-field="price">
              <label class="form-label" for="price">Harga (Rp)</label>
              <input class="form-control @error('price') is-invalid @enderror" id="price" name="price" type="text" inputmode="numeric" data-currency value="{{ old('price', $examination?->price !== null ? (float) $examination->price : null) }}" placeholder="0">
              @error('price')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>
            <div class="col-md-6">
              <label class="form-label" for="unit">Satuan</label>
              <input class="form-control" id="unit" name="unit" value="{{ old('unit', $examination?->unit) }}" maxlength="50">
            </div>
            <div class="col-md-6">
              <label class="form-label" for="method">Metode</label>
              <input class="form-control" id="method" name="method" value="{{ old('method', $examination?->method) }}" maxlength="255">
            </div>
            <div class="col-12" data-field="number">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label" for="referenceMin">Batas normal minimum</label>
                  <input class="form-control @error('reference_min') is-invalid @enderror" id="referenceMin" type="number" step="any" name="reference_min" value="{{ old('reference_min', $referenceMin) }}">
                  @error('reference_min')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="referenceMax">Batas normal maksimum</label>
                  <input class="form-control @error('reference_max') is-invalid @enderror" id="referenceMax" type="number" step="any" name="reference_max" value="{{ old('reference_max', $referenceMax) }}">
                  @error('reference_max')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
              </div>
              <div class="form-text">Boleh kosong sampai nilai rujukan disetujui, atau isi salah satu batas jika rujukan hanya memiliki batas minimum atau maksimum.</div>
            </div>
            <div class="col-12" data-field="choice">
              <div class="row g-3">
                <div class="col-md-6">
                  <label class="form-label" for="normalOptions">Pilihan normal</label>
                  <textarea class="form-control @error('normal_options') is-invalid @enderror" id="normalOptions" name="normal_options" rows="4" placeholder="Satu pilihan per baris">{{ old('normal_options', $normalOptions) }}</textarea>
                  @error('normal_options')<div class="invalid-feedback">{{ $message }}</div>@enderror
                </div>
                <div class="col-md-6">
                  <label class="form-label" for="abnormalOptions">Pilihan abnormal</label>
                  <textarea class="form-control" id="abnormalOptions" name="abnormal_options" rows="4" placeholder="Satu pilihan per baris">{{ old('abnormal_options', $abnormalOptions) }}</textarea>
                </div>
              </div>
            </div>
          </div>
        </section>
      </div>
    </div>
    <div class="d-flex justify-content-end gap-2 mt-3">
      <a class="btn btn-outline-secondary" href="{{ route('master.examinations.index') }}">Batal</a>
      <button class="btn btn-naleya" type="submit">Simpan Master Pemeriksaan</button>
    </div>
  </form>
@endsection

@push('scripts')
  <script>
    (() => {
      const formatCurrency = (input) => {
        const digits = input.value.replace(/\D/g, '').replace(/^0+(?=\d)/, '');
        input.value = digits ? new Intl.NumberFormat('id-ID').format(Number(digits)) : '';
      };
      document.querySelectorAll('[data-currency]').forEach((input) => {
        formatCurrency(input);
        input.addEventListener('input', () => formatCurrency(input));
      });
      document.querySelector('form').addEventListener('submit', () => {
        document.querySelectorAll('[data-currency]').forEach((input) => {
          input.value = input.value.replace(/\D/g, '');
        });
      });
      const nodeType = document.getElementById('nodeType');
      const headerType = document.getElementById('headerType');
      const resultType = document.getElementById('resultType');
      const isOrderable = document.getElementById('isOrderable');
      const toggle = (name, visible) => {
        document.querySelectorAll(`[data-field="${name}"]`).forEach((group) => {
          group.hidden = !visible;
          group.style.display = visible ? '' : 'none';
          group.querySelectorAll('input, select, textarea').forEach((field) => { field.disabled = !visible; });
        });
      };
      const refresh = () => {
        const header = nodeType.value === 'header';
        toggle('header', header);
        toggle('examination', !header);
        toggle('parent', !header);
        toggle('package', header && headerType.value === 'package');
        toggle('price', !header && isOrderable.value === '1');
        toggle('number', !header && resultType.value === 'number');
        toggle('choice', !header && resultType.value === 'choice');
      };
      [nodeType, headerType, resultType, isOrderable].forEach((field) => field.addEventListener('change', refresh));
      refresh();
    })();
  </script>
@endpush
