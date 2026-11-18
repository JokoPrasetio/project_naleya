@extends('partials.layout')

@section('page-title', 'Master Pemeriksaan')

@section('content')
  <div class="page-heading">
    <div>
      <h1 class="access-page-title">Master Pemeriksaan</h1>
      <p>Lihat seluruh layanan atau paket dan buka turunan pemeriksaannya di tabel.</p>
    </div>
    @can('master.examinations.manage')
      <a class="btn btn-naleya" href="{{ route('master.examinations.create') }}"><i class="bi bi-plus-lg me-1"></i> Tambah node</a>
    @endcan
  </div>

  <section class="section-card table-card master-table-card">
    <div class="master-table-head">
      <nav class="master-tabs" aria-label="Jenis master">
        <a class="master-tab {{ $activeType === 'service' ? 'active' : '' }}"
          href="{{ route('master.examinations.index', ['type' => 'service']) }}"
          @if ($activeType === 'service') aria-current="page" @endif>
          Layanan <span>{{ $roots->where('header_type', 'service')->count() }}</span>
        </a>
        <a class="master-tab {{ $activeType === 'package' ? 'active' : '' }}"
          href="{{ route('master.examinations.index', ['type' => 'package']) }}"
          @if ($activeType === 'package') aria-current="page" @endif>
          Paket <span>{{ $roots->where('header_type', 'package')->count() }}</span>
        </a>
      </nav>
      <p class="section-subtitle mb-0">{{ $activeType === 'package' ? 'Semua paket ditampilkan bersama layanan anggotanya.' : 'Semua layanan ditampilkan bersama header dan pemeriksaannya.' }}</p>
    </div>
    <div class="master-table-search">
      <label class="visually-hidden" for="masterTableSearch">Cari di tabel pemeriksaan</label>
      <i class="bi bi-search" aria-hidden="true"></i>
      <input class="form-control" id="masterTableSearch" type="search" placeholder="Cari nama, kode, atau nilai rujukan" autocomplete="off">
      <span class="text-muted small">Hasil pencarian ditampilkan beserta induknya.</span>
    </div>
    <div class="table-responsive">
      <table class="table table-modern align-middle mb-0" id="masterExaminationTable">
        <thead>
          <tr>
            <th scope="col">Nama</th>
            <th scope="col">Jenis</th>
            <th scope="col">Kode</th>
            <th scope="col">Tipe hasil</th>
            <th scope="col">Nilai rujukan</th>
            <th scope="col" class="text-end">Harga</th>
            <th scope="col">Status</th>
            @can('master.examinations.manage')<th scope="col">Aksi</th>@endcan
          </tr>
        </thead>
        <tbody>
          @forelse ($tabRoots as $node)
            @include('master.examinations.row', ['node' => $node, 'depth' => 0, 'treeKey' => (string) $node->id, 'tableParentKey' => null, 'childrenByParent' => $childrenByParent, 'totals' => $totals, 'inPackage' => false])
          @empty
            <tr><td colspan="{{ auth()->user()->can('master.examinations.manage') ? 8 : 7 }}" class="text-center py-4 text-muted">Belum ada {{ $activeType === 'package' ? 'paket' : 'layanan' }}.</td></tr>
          @endforelse
          @if ($tabRoots->isNotEmpty())
            <tr class="master-search-empty" data-search-empty hidden><td colspan="{{ auth()->user()->can('master.examinations.manage') ? 8 : 7 }}" class="text-center py-4 text-muted">Tidak ada hasil yang cocok.</td></tr>
          @endif
        </tbody>
      </table>
    </div>
  </section>
@endsection

@push('scripts')
  <script src="{{ asset('scripts/pages/master-examinations.js') }}?v={{ filemtime(public_path('scripts/pages/master-examinations.js')) }}"></script>
@endpush
