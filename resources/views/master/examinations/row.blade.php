@php
  $kind = match ($node->node_type) {
    'header' => 'Header',
    'sub_header' => 'Sub Header',
    default => 'Pemeriksaan',
  };
  $range = $node->result_type === 'number' ? $node->referenceRanges->first() : null;
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
    @if ($node->result_type === 'number')
      @if ($range)
        @if ($range->min_value !== null && $range->max_value !== null)
          {{ $range->min_value }}–{{ $range->max_value }} {{ $node->unit }}
        @elseif ($range->min_value !== null)
          ≥ {{ $range->min_value }} {{ $node->unit }}
        @else
          ≤ {{ $range->max_value }} {{ $node->unit }}
        @endif
      @else
        <span class="text-muted">Belum diatur</span>
      @endif
    @elseif ($node->result_type === 'choice')
      @php
        $normalOptions = $node->resultOptions->where('is_normal', true)->pluck('label')->implode(', ');
        $abnormalOptions = $node->resultOptions->where('is_normal', false)->pluck('label')->implode(', ');
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
          <a class="btn btn-sm btn-outline-secondary" href="{{ route('master.examinations.edit', $node) }}">Ubah</a>
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
