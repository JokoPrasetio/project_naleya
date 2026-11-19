@extends('partials.layout')

@section('page-title', 'Master Pemeriksaan')

@section('content')
  <div class="page-heading">
    <div><h1 class="access-page-title">Master Pemeriksaan</h1><p>Kelola data pemeriksaan, jenisnya, harga, nilai normal, nilai abnormal, dan form hasil.</p></div>
    @can('master.examinations.manage')
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-primary" href="{{ route('master.examinations.bulk-create') }}"><i class="bi bi-list-ul me-1"></i> Input Banyak</a>
        <button class="btn btn-outline-success" id="importExaminationButton" type="button"><i class="bi bi-file-earmark-spreadsheet me-1"></i> Impor Excel</button>
        <button class="btn btn-naleya" id="addExaminationButton" type="button"><i class="bi bi-plus-lg me-1"></i> Tambah Pemeriksaan</button>
      </div>
    @endcan
  </div>
  @if(request()->integer('bulk_saved') > 0)
    <div class="alert alert-success alert-dismissible fade show" role="alert">{{ request()->integer('bulk_saved') }} baris pemeriksaan berhasil disimpan.<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Tutup"></button></div>
  @endif

  <section class="section-card table-card">
    <div class="table-responsive access-datatable-wrap">
      <div class="p-3 border-bottom d-flex flex-wrap gap-2 align-items-center justify-content-between">
        <div><strong>Daftar pohon pemeriksaan</strong><div class="small text-muted">Klik induk untuk membuka atau menutup turunannya.</div></div>
        <input class="form-control" id="examinationTreeSearch" type="search" placeholder="Cari nama atau kode pemeriksaan" style="max-width: 320px">
      </div>
      <table class="table table-modern align-middle w-100 mb-0" id="examinationsTable" data-source="{{ route('master.examinations.data') }}" data-types-source="{{ route('master.examinations.types') }}" data-parents-source="{{ route('master.examinations.parents') }}" data-can-manage="{{ auth()->user()->can('master.examinations.manage') ? '1' : '0' }}">
        <thead><tr><th>Nama / Struktur</th><th>Kode</th><th>Jenis</th><th>Tipe Node</th><th>Harga</th><th>Status</th><th class="text-end">Aksi</th></tr></thead><tbody>
          @foreach($examinations as $examination)
            <tr class="examination-tree-row node-{{ $examination->node_type }}" data-tree-id="{{ $examination->id }}" data-parent-id="{{ $examination->parent_id }}" data-node-type="{{ $examination->node_type }}" data-name="{{ mb_strtolower($examination->name) }}" data-code="{{ mb_strtolower($examination->code_examination ?? '') }}" data-row="{{ json_encode(['id' => $examination->id, 'name' => $examination->name, 'code' => $examination->code_examination, 'type' => $examination->type?->name, 'node_type' => $examination->node_type, 'price' => $examination->price, 'active' => $examination->active]) }}">
              <td class="tree-name-cell"><span class="tree-indent" data-depth="0"></span>@if($examination->node_type !== 'item')<button class="btn btn-sm btn-light border me-2" type="button" data-tree-toggle="{{ $examination->id }}" aria-expanded="false"><i class="bi bi-chevron-right"></i></button>@else<span class="tree-item-mark me-2"><i class="bi bi-dot"></i></span>@endif<strong>{{ $examination->name }}</strong></td><td>{{ $examination->code_examination ?: '—' }}</td><td>{{ $examination->type?->name ?: '—' }}</td><td><span class="node-type-badge">{{ match ($examination->node_type) { 'header' => 'Header', 'sub_header' => 'Sub Header', default => 'Item' } }}</span></td><td>{{ $examination->price === null ? '—' : 'Rp'.number_format((float) $examination->price, 0, ',', '.') }}</td><td><span class="status {{ $examination->active ? 'confirmed' : 'waiting' }}">{{ $examination->active ? 'Aktif' : 'Nonaktif' }}</span></td><td class="text-end text-nowrap">@can('master.examinations.manage')<button class="btn btn-sm btn-outline-secondary" type="button" data-edit-examination="{{ $examination->id }}">Ubah</button> <button class="btn btn-sm btn-outline-danger" type="button" data-delete-examination="{{ $examination->id }}">Hapus</button>@endcan</td>
            </tr>
          @endforeach
        </tbody>
      </table>
    </div>
  </section>

  @can('master.examinations.manage')
    <div class="modal fade" id="bulkExaminationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-store-url="{{ route('master.examinations.bulk') }}">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content access-modal"><form id="bulkExaminationForm" data-no-page-loading>
        @csrf
        <div class="modal-header"><div><h2 class="modal-title fs-5">Input Banyak Pemeriksaan</h2><p class="small text-muted mb-0">Isi satu induk dan jenis, lalu tambahkan beberapa baris pemeriksaan.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body"><div class="alert alert-danger" data-bulk-errors hidden></div><div class="row g-3 mb-3"><div class="col-md-6"><label class="form-label">Induk (opsional)</label><select class="form-select" name="parent_id" data-bulk-parent><option value="0">Tanpa induk</option></select></div><div class="col-md-6"><label class="form-label">Jenis pemeriksaan</label><select class="form-select" name="type_examination_id" data-bulk-type required><option value="">Pilih jenis</option></select></div></div>
          <div class="table-responsive"><table class="table table-sm table-bordered align-middle"><thead><tr><th>Nama pemeriksaan *</th><th>Kode</th><th>Nama barcode</th><th>Tipe</th><th>Harga</th><th>Kode mesin</th><th>Metode</th><th>Satuan</th><th>Status</th><th></th></tr></thead><tbody id="bulkExaminationRows"></tbody></table></div><button class="btn btn-sm btn-outline-primary" type="button" data-add-bulk-row><i class="bi bi-plus-lg me-1"></i>Tambah baris</button>
        </div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button><button class="btn btn-naleya" type="submit" data-bulk-save>Simpan semua</button></div>
      </form></div></div>
    </div>
    <div class="modal fade" id="importExaminationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-import-url="{{ route('master.examinations.import') }}" data-template-url="{{ route('master.examinations.template') }}">
      <div class="modal-dialog modal-lg modal-dialog-centered"><div class="modal-content access-modal"><form id="importExaminationForm" enctype="multipart/form-data" data-no-page-loading>
        @csrf<div class="modal-header"><div><h2 class="modal-title fs-5">Impor Pemeriksaan dari Excel</h2><p class="small text-muted mb-0">Unggah file `.xlsx` atau CSV memakai susunan kolom template.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
        <div class="modal-body"><div class="alert alert-danger" data-import-errors hidden></div><div class="alert alert-info">Unduh template Excel, isi sheet <strong>Data Pemeriksaan</strong>, lalu pilih jenis dan induk untuk semua baris. Kolom Nama Pemeriksaan dan Tipe Node wajib diisi. File dapat memuat maksimal 500 baris.</div><div class="mb-3"><a class="btn btn-sm btn-outline-success" href="{{ route('master.examinations.template') }}" download="template-pemeriksaan.xlsx" data-no-page-loading><i class="bi bi-download me-1"></i>Unduh template Excel (.xlsx)</a></div><div class="row g-3"><div class="col-md-6"><label class="form-label">Induk (opsional)</label><select class="form-select" name="parent_id" data-import-parent><option value="0">Tanpa induk</option></select></div><div class="col-md-6"><label class="form-label">Jenis pemeriksaan</label><select class="form-select" name="type_examination_id" data-import-type required><option value="">Pilih jenis</option></select></div><div class="col-12"><label class="form-label">File Excel / CSV</label><input class="form-control" type="file" name="file" accept=".xlsx,.csv,.txt" required></div></div></div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button><button class="btn btn-naleya" type="submit" data-import-save>Impor data</button></div>
      </form></div></div>
    </div>
    <div class="modal fade" id="examinationModal" tabindex="-1" aria-labelledby="examinationModalTitle" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false" data-store-url="{{ route('master.examinations.store') }}" data-update-base="{{ route('master.examinations.index') }}">
      <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable"><div class="modal-content access-modal">
        <form id="examinationForm" method="POST" action="{{ route('master.examinations.store') }}" data-no-page-loading>
          @csrf
          <input type="hidden" id="examinationMethod" name="_method" value="PUT" disabled>
          <div class="modal-header"><div><h2 class="modal-title fs-5" id="examinationModalTitle">Tambah Pemeriksaan</h2><p class="small text-muted mb-0">Isi data dasar, lalu atur nilai dan field hasil bila diperlukan.</p></div><button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button></div>
          <div class="modal-body">
            <div class="alert alert-danger" id="examinationErrors" hidden></div>
            <ul class="nav nav-tabs mb-3" role="tablist">
              <li class="nav-item"><button class="nav-link active" type="button" data-bs-toggle="tab" data-bs-target="#exam-main-pane">Data utama</button></li>
              <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#exam-normal-pane">Nilai normal</button></li>
              <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#exam-abnormal-pane">Nilai abnormal</button></li>
              <li class="nav-item"><button class="nav-link" type="button" data-bs-toggle="tab" data-bs-target="#exam-fields-pane">Form hasil</button></li>
            </ul>
            <div class="tab-content">
              <div class="tab-pane fade show active" id="exam-main-pane">
                <div class="row g-3">
                  <div class="col-md-4"><label class="form-label">Induk pemeriksaan</label><select class="form-select" name="parent_id" data-exam-field="parent_id"><option value="0">Tanpa induk</option></select></div>
                  <div class="col-md-4"><label class="form-label">Jenis pemeriksaan</label><select class="form-select" name="type_examination_id" data-exam-field="type_examination_id" required><option value="">Pilih jenis</option></select></div>
                  <div class="col-md-4"><label class="form-label">Tipe node</label><select class="form-select" name="node_type" data-exam-field="node_type"><option value="header">Header</option><option value="sub_header">Sub Header</option><option value="item">Item</option></select></div>
                  <div class="col-md-6"><label class="form-label">Nama pemeriksaan</label><input class="form-control" name="name" data-exam-field="name" required maxlength="255"></div>
                  <div class="col-md-3"><label class="form-label">Kode pemeriksaan</label><input class="form-control" name="code_examination" data-exam-field="code_examination" maxlength="255"></div>
                  <div class="col-md-3"><label class="form-label">Nama barcode</label><input class="form-control" name="barcode_name" data-exam-field="barcode_name" maxlength="255"></div>
                  <div class="col-md-4"><label class="form-label">Kode machine</label><input class="form-control" name="code_machine" data-exam-field="code_machine" maxlength="255"></div>
                  <div class="col-md-4"><label class="form-label">Harga (Rp)</label><input class="form-control" type="number" min="0" step="0.01" name="price" data-exam-field="price"></div>
                  <div class="col-md-4"><label class="form-label">Urutan</label><input class="form-control" type="number" min="0" name="sort_order" data-exam-field="sort_order" value="0"></div>
                  <div class="col-md-6"><label class="form-label">Metode pemeriksaan</label><input class="form-control" name="method_examination" data-exam-field="method_examination" maxlength="255"></div>
                  <div class="col-md-6"><label class="form-label">Satuan</label><input class="form-control" name="unit" data-exam-field="unit" maxlength="255"></div>
                  <div class="col-md-4"><label class="form-label">Status</label><select class="form-select" name="active" data-exam-field="active"><option value="1">Aktif</option><option value="0">Nonaktif</option></select></div>
                </div>
              </div>
              <div class="tab-pane fade" id="exam-normal-pane">
                <div class="d-flex justify-content-between align-items-center mb-2"><div><strong>Nilai normal</strong><div class="small text-muted">Dapat diisi untuk beberapa kelompok umur atau klasifikasi.</div></div><button class="btn btn-sm btn-outline-primary" type="button" data-add-normal>Tambah nilai</button></div>
                <div class="d-grid gap-2" id="normalValueRows"></div>
              </div>
              <div class="tab-pane fade" id="exam-abnormal-pane">
                <div class="d-flex justify-content-between align-items-center mb-2"><div><strong>Nilai abnormal</strong><div class="small text-muted">Atur kondisi, nilai, kesimpulan, saran, dan ringkasan.</div></div><button class="btn btn-sm btn-outline-primary" type="button" data-add-abnormal>Tambah nilai</button></div>
                <div class="d-grid gap-2" id="abnormalValueRows"></div>
              </div>
              <div class="tab-pane fade" id="exam-fields-pane">
                <div class="d-flex justify-content-between align-items-center mb-2"><div><strong>Field hasil pemeriksaan</strong><div class="small text-muted">Contoh field hasil, deskripsi, kesan, saran, atau gambar.</div></div><button class="btn btn-sm btn-outline-primary" type="button" data-add-result-field>Tambah field</button></div>
                <div class="d-grid gap-2" id="resultFieldRows"></div>
              </div>
            </div>
          </div>
          <div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button><button class="btn btn-naleya" id="saveExaminationButton" type="submit">Simpan pemeriksaan</button></div>
        </form>
      </div></div>
    </div>
    <div class="modal fade" id="deleteExaminationModal" tabindex="-1" aria-hidden="true" data-bs-backdrop="static" data-bs-keyboard="false"><div class="modal-dialog modal-dialog-centered"><div class="modal-content access-modal"><div class="modal-header"><h2 class="modal-title fs-5">Hapus pemeriksaan</h2><button class="btn-close" type="button" data-bs-dismiss="modal" aria-label="Tutup"></button></div><div class="modal-body">Hapus <strong id="deleteExaminationName"></strong>?</div><div class="modal-footer"><button class="btn btn-outline-secondary" type="button" data-bs-dismiss="modal">Batal</button><form id="deleteExaminationForm" method="POST" data-base-url="{{ route('master.examinations.index') }}" data-no-page-loading>@csrf @method('DELETE')<button class="btn btn-danger" type="submit">Ya, hapus</button></form></div></div></div></div>
  @endcan
  <div class="toast-container position-fixed top-0 end-0 p-3 app-toast-container"><div class="toast app-toast" id="examinationToast" data-bs-delay="5000"><div class="toast-body"><i class="bi bi-check-circle app-toast-icon"></i><div class="flex-grow-1"><strong>Berhasil</strong><small></small></div><button type="button" class="btn-close" data-bs-dismiss="toast" aria-label="Tutup"></button></div></div></div>
@endsection

@push('styles')
  <style>
    .examination-tree-row.node-header > td { background: #edf7f5; font-weight: 600; }
    .examination-tree-row.node-sub_header > td { background: #f7faf9; }
    .examination-tree-row.node-item > td { color: #475569; }
    .tree-name-cell { white-space: nowrap; }
    .tree-indent { vertical-align: middle; }
    .tree-item-mark { display: inline-flex; width: 32px; justify-content: center; color: #94a3b8; }
    .node-type-badge { display: inline-block; border-radius: 999px; padding: .2rem .6rem; background: #e2e8f0; color: #475569; font-size: .75rem; font-weight: 600; white-space: nowrap; }
    .node-header .node-type-badge { background: #c9eee7; color: #11665e; }
    .node-sub_header .node-type-badge { background: #e8edf5; color: #42536b; }
    .node-item .node-type-badge { background: #f1f5f9; color: #64748b; }
  </style>
@endpush

@push('scripts')
  @vite('resources/js/examinations.js')
@endpush
