<?php

declare(strict_types=1);

namespace App\Http\Controllers\Branch;

use App\Http\Controllers\Controller;
use App\Http\Requests\WarehouseStoreRequest;
use App\Http\Requests\WarehouseUpdateRequest;
use App\Models\Warehouse;
use Illuminate\Http\Request;

class WarehouseController extends Controller
{
    public function index(Request $request)
    {
        $per = min(max($request->integer('per_page', 20), 1), 100);
        $rows = Warehouse::query()
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->q.'%'))
            ->where('branch_id', (int) $request->attributes->get('branch_id'))
            ->orderByDesc('id')->paginate($per);

        return $this->ok($rows);
    }

    public function store(WarehouseStoreRequest $request)
    {
        $data = $request->validated();
        $row = Warehouse::create($data + ['branch_id' => (int) $request->attributes->get('branch_id')]);

        return $this->ok($row, __('Created'), 201);
    }

    public function show(Warehouse $warehouse)
    {
        // Security: Ensure warehouse belongs to current branch
        $branchId = (int) request()->attributes->get('branch_id');
        abort_if($warehouse->branch_id !== $branchId, 404, 'Warehouse not found in this branch');

        return $this->ok($warehouse);
    }

    public function update(WarehouseUpdateRequest $request, Warehouse $warehouse)
    {
        // Security: Ensure warehouse belongs to current branch
        $branchId = (int) $request->attributes->get('branch_id');
        abort_if($warehouse->branch_id !== $branchId, 404, 'Warehouse not found in this branch');

        $warehouse->fill($request->validated())->save();

        return $this->ok($warehouse, __('Updated'));
    }

    public function destroy(Warehouse $warehouse)
    {
        // Security: Ensure warehouse belongs to current branch
        $branchId = (int) request()->attributes->get('branch_id');
        abort_if($warehouse->branch_id !== $branchId, 404, 'Warehouse not found in this branch');

        $warehouse->delete();

        return $this->ok(null, __('Deleted'));
    }
}
