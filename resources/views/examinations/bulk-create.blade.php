@extends('partials.layout')

@section('page-title', 'Input Banyak Pemeriksaan')

@section('content')
  <div class="page-heading">
    <div>
      <h1 class="access-page-title">Input Banyak Pemeriksaan</h1>
      <p>Atur setiap pemeriksaan dalam pohon. Pilih induk yang sudah ada atau jadikan baris lain sebagai induknya.</p>
    </div>
    <a class="btn btn-outline-secondary" href="{{ route('master.examinations.index') }}"><i class="bi bi-arrow-left me-1"></i>Kembali ke daftar</a>
  </div>

  <form id="bulkExaminationForm" action="{{ route('master.examinations.bulk') }}" method="POST" data-index-url="{{ route('master.examinations.index') }}" data-no-page-loading data-types='@json($types->map(fn ($type) => ["id" => $type->id, "name" => $type->name]))' data-parents='@json($parents->map(fn ($parent) => ["id" => $parent->id, "name" => $parent->name, "node_type" => $parent->node_type, "type_id" => $parent->type_examination_id]))'>
    @csrf
    <section class="section-card p-3 mb-3">
      <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h2 class="h6 mb-1">Petunjuk susunan</h2><div class="small text-muted">Header dapat memiliki Sub Header atau Item. Sub Header dapat memiliki Item. Item tidak dapat menjadi induk.</div></div>
        <div class="d-flex flex-wrap gap-2 small"><span class="bulk-legend header">Header</span><span class="bulk-legend sub-header">Sub Header</span><span class="bulk-legend item">Item Pemeriksaan</span></div>
      </div>
      <div class="alert alert-light border mb-0"><strong>Contoh:</strong> baris “Darah Lengkap” pilih induk “Hematologi”; lalu baris “Hemoglobin” pilih induk “Baris: Darah Lengkap”. Jenis untuk baris turunan akan mengikuti induknya.</div>
    </section>

    <section class="section-card table-card">
      <div class="p-3 border-bottom d-flex flex-wrap justify-content-between align-items-center gap-2"><div><h2 class="h6 mb-1">Susunan pemeriksaan</h2><div class="small text-muted">Urutan baris menentukan urutan tampil pada tingkat induk yang sama.</div></div><button class="btn btn-sm btn-outline-primary" type="button" data-add-bulk-row><i class="bi bi-plus-lg me-1"></i>Tambah baris</button></div>
      <div class="table-responsive"><table class="table table-modern align-middle mb-0"><thead><tr><th style="min-width:190px">Tipe Node</th><th style="min-width:240px">Nama Pemeriksaan</th><th style="min-width:240px">Induk / Bagian</th><th style="min-width:180px">Jenis</th><th style="min-width:145px">Kode</th><th style="min-width:135px">Harga</th><th style="min-width:150px">Kode Mesin</th><th style="min-width:150px">Metode</th><th style="min-width:110px">Satuan</th><th style="min-width:115px">Status</th><th></th></tr></thead><tbody id="bulkExaminationRows"></tbody></table></div>
      <div class="p-3 d-flex flex-wrap justify-content-between align-items-center gap-2"><div class="small text-muted" id="bulkStructureSummary">Tambahkan baris, lalu tentukan induk untuk setiap sub header dan item.</div><div class="d-flex gap-2"><a class="btn btn-outline-secondary" href="{{ route('master.examinations.index') }}">Batal</a><button class="btn btn-naleya" type="submit" data-bulk-save>Simpan semua pemeriksaan</button></div></div>
    </section>
    <div class="alert alert-danger mt-3" id="bulkExaminationErrors" hidden></div>
  </form>
@endsection

@push('styles')
  <style>
    .bulk-legend { border-radius: 999px; padding: .3rem .7rem; font-weight: 600; }
    .bulk-legend.header { color: #11665e; background: #c9eee7; }
    .bulk-legend.sub-header { color: #42536b; background: #e8edf5; }
    .bulk-legend.item { color: #64748b; background: #f1f5f9; }
    .bulk-row-header { background: #edf7f5; }
    .bulk-row-sub_header { background: #f7faf9; }
    .bulk-row-item .bulk-name-wrap { padding-left: 1rem; }
  </style>
@endpush

@push('scripts')
  @vite('resources/js/examination-bulk.js')
@endpush
