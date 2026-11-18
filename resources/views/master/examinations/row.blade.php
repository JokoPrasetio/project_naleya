@php
  $kind = match ($node->node_type) {
    'header' => 'Header',
    'sub_header' => 'Sub Header',
    default => 'Pemeriksaan',
  };
  $referenceMin = $node->result_type === 'number' ? $node->normalValues->firstWhere('normal_value_type', 'minimum')?->value : null;
  $referenceMax = $node->result_type === 'number' ? $node->normalValues->firstWhere('normal_value_type', 'maximum')?->value : null;
  $classifications = $node->normalValues->pluck('classification')
    ->merge($node->abnormalValues->pluck('classification'))
    ->filter()->unique()->values();
  $children = $node->header_type === 'package'
    ? $node->packageServices
    : $childrenByParent->get((string) $node->id, collect());
@endphp
<tr class="master-tree-row" data-tree-id="{{ $treeKey }}" data-tree-parent="{{ $tableParentKey }}" data-tree-expanded="true">
  <td>
    <div class="master-row-name" style="padding-left: {{ 0.75 + ($depth * 1.25) }}rem;">
      @if ($children->isNotEmpty())
        <button class="master-tree-toggle" type="button" data-tree-toggle aria-expanded="true" aria-label="Tutup turunan {{ $node->name }}">
          <i class="bi bi-chevron-down" aria-hidden="true"></i>
        </button>
      @else
        <span class="master-tree-spacer" aria-hidden="true"></span>
      @endif
      <span class="{{ in_array($node->node_type, ['header', 'sub_header'], true) ? 'fw-semibold' : '' }}">{{ $node->name }}</span>
    </div>
  </td>
  <td><span class="badge text-bg-light">{{ $kind }}</span></td>
  <td>{{ $node->code ?: '—' }}</td>
  <td>{{ match ($node->result_type) { 'number' => 'Angka', 'choice' => 'Pilihan', 'text' => 'Teks', default => '—' } }}</td>
  <td>
    @if ($classifications->isNotEmpty())
      <span class="d-block">{{ $classifications->count() }} klasifikasi</span>
      <span class="text-muted small">{{ $classifications->take(3)->implode(', ') }}@if ($classifications->count() > 3) …@endif</span>
    @elseif ($node->result_type === 'number')
      @if ($referenceMin !== null || $referenceMax !== null)
        @if ($referenceMin !== null && $referenceMax !== null)
          {{ $referenceMin }}–{{ $referenceMax }} {{ $node->unit }}
        @elseif ($referenceMin !== null)
          ≥ {{ $referenceMin }} {{ $node->unit }}
        @else
          ≤ {{ $referenceMax }} {{ $node->unit }}
        @endif
      @else
        <span class="text-muted">Belum diatur</span>
      @endif
    @elseif ($node->result_type === 'choice')
      @php
        $normalOptions = $node->normalValues->where('normal_value_type', 'choice')->pluck('value')->implode(', ');
        $abnormalOptions = $node->abnormalValues->where('type', 'choice')->pluck('value')->implode(', ');
      @endphp
      @if ($normalOptions)<span class="d-block">Normal: {{ $normalOptions }}</span>@endif
      @if ($abnormalOptions)<span class="d-block">Abnormal: {{ $abnormalOptions }}</span>@endif
      @if (! $normalOptions && ! $abnormalOptions)<span class="text-muted">Belum diatur</span>@endif
    @else
      <span class="text-muted">—</span>
    @endif
  </td>
  <td class="text-end text-nowrap">
    @if ($node->is_orderable || in_array($node->node_type, ['header', 'sub_header'], true))
      Rp{{ number_format($totals[$node->id] ?? 0, 0, ',', '.') }}
    @else
      <span class="text-muted">—</span>
    @endif
  </td>
  <td><span class="badge {{ $node->is_active ? 'text-bg-success' : 'text-bg-secondary' }}">{{ $node->is_active ? 'Aktif' : 'Nonaktif' }}</span></td>
  @can('master.examinations.manage')
    <td>
      @if ($inPackage)
        <span class="text-muted">—</span>
      @else
        <div class="d-flex gap-1">
          <button class="btn btn-sm btn-outline-secondary" type="button" data-edit-examination="{{ $node->id }}">Ubah</button>
          <form method="POST" action="{{ route('master.examinations.destroy', $node) }}" onsubmit="return confirm('Hapus node ini?')">
            @csrf
            @method('DELETE')
            <button class="btn btn-sm btn-outline-danger" type="submit">Hapus</button>
          </form>
        </div>
      @endif
    </td>
  @endcan
</tr>
@foreach ($children as $child)
  @include('master.examinations.row', ['node' => $child, 'depth' => $depth + 1, 'treeKey' => $treeKey.'-'.$child->id, 'tableParentKey' => $treeKey, 'childrenByParent' => $childrenByParent, 'totals' => $totals, 'inPackage' => $inPackage || $node->header_type === 'package'])
@endforeach
