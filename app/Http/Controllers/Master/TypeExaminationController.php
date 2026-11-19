<?php

namespace App\Http\Controllers\Master;

use App\Http\Controllers\Controller;
use App\Http\Requests\Master\StoreTypeExaminationRequest;
use App\Models\TypeExamination;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TypeExaminationController extends Controller
{
    public function index(): View
    {
        return view('master.type_examinations.index');
    }

    public function data(Request $request): JsonResponse
    {
        $draw = max(0, (int) $request->input('draw', 0));
        $start = max(0, (int) $request->input('start', 0));
        $length = min(100, max(1, (int) $request->input('length', 10)));
        $searchInput = $request->input('search.value', '');
        $search = is_string($searchInput) ? trim(mb_substr($searchInput, 0, 100)) : '';
        $sortableColumns = [0 => 'name', 1 => 'status'];
        $orderColumn = $sortableColumns[(int) $request->input('order.0.column', 0)] ?? 'name';
        $orderDirection = $request->input('order.0.dir') === 'desc' ? 'desc' : 'asc';

        $query = TypeExamination::query()
            ->when($search !== '', fn ($query) => $query->where('name', 'like', "%{$search}%"));

        $total = TypeExamination::query()->count();
        $filtered = (clone $query)->count();
        $types = $query
            ->orderBy($orderColumn, $orderDirection)
            ->orderBy('id')
            ->skip($start)
            ->take($length)
            ->get();

        return response()->json([
            'draw' => $draw,
            'recordsTotal' => $total,
            'recordsFiltered' => $filtered,
            'data' => $types->map(fn (TypeExamination $type): array => [
                'id' => $type->id,
                'name' => $type->name,
                'status' => $type->status,
            ])->all(),
        ]);
    }

    public function store(StoreTypeExaminationRequest $request): JsonResponse|RedirectResponse
    {
        $items = $request->validated('items');

        DB::transaction(function () use ($items): void {
            foreach ($items as $item) {
                TypeExamination::create($item);
            }
        });

        $message = count($items) === 1
            ? 'Jenis pemeriksaan berhasil ditambahkan.'
            : count($items).' jenis pemeriksaan berhasil ditambahkan.';

        if ($request->expectsJson()) {
            return response()->json(['message' => $message], 201);
        }

        return redirect()->route('master.type_examinations.index')->with('success', $message);
    }

    public function update(StoreTypeExaminationRequest $request, TypeExamination $typeExamination): JsonResponse|RedirectResponse
    {
        $typeExamination->update($request->safe()->only(['name', 'status']));

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Jenis pemeriksaan berhasil diperbarui.']);
        }

        return redirect()->route('master.type_examinations.index')->with('success', 'Jenis pemeriksaan berhasil diperbarui.');
    }

    public function destroy(Request $request, TypeExamination $typeExamination): JsonResponse|RedirectResponse
    {
        $typeExamination->delete();

        if ($request->expectsJson()) {
            return response()->json(['message' => 'Jenis pemeriksaan berhasil dihapus.']);
        }

        return redirect()->route('master.type_examinations.index')->with('success', 'Jenis pemeriksaan berhasil dihapus.');
    }
}
