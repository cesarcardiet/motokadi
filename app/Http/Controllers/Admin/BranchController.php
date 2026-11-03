<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreBranchRequest;
use App\Models\Branch;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class BranchController extends Controller
{
    public function index(): View
    {
        return view('admin.branches.index', [
            'branches' => Branch::query()->withCount(['users', 'applications'])->orderBy('name')->paginate(8),
        ]);
    }

    public function create(): View
    {
        return view('admin.branches.form', ['branch' => new Branch(['is_active' => true])]);
    }

    public function store(StoreBranchRequest $request): RedirectResponse
    {
        Branch::query()->create($this->payload($request));

        return redirect()->route('admin.branches.index')->with('status', 'Sede creada.');
    }

    public function edit(Branch $branch): View
    {
        return view('admin.branches.form', compact('branch'));
    }

    public function update(StoreBranchRequest $request, Branch $branch): RedirectResponse
    {
        $branch->update($this->payload($request));

        return redirect()->route('admin.branches.index')->with('status', 'Sede actualizada.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(StoreBranchRequest $request): array
    {
        return [
            'name' => $request->string('name')->toString(),
            'city' => $request->string('city')->toString(),
            'address' => $request->string('address')->toString(),
            'phone' => $request->input('phone'),
            'is_active' => $request->boolean('is_active'),
        ];
    }
}
