<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreUserRequest;
use App\Models\Branch;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->with(['role', 'branch'])->orderBy('name')->paginate(8),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', [
            'userModel' => new User(['is_active' => true]),
            'roles' => Role::query()->orderBy('name')->get(),
            'branches' => Branch::query()->orderBy('name')->get(),
        ]);
    }

    public function store(StoreUserRequest $request): RedirectResponse
    {
        User::query()->create($this->payload($request, true));

        return redirect()->route('admin.users.index')->with('status', 'Usuario creado.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', [
            'userModel' => $user,
            'roles' => Role::query()->orderBy('name')->get(),
            'branches' => Branch::query()->orderBy('name')->get(),
        ]);
    }

    public function update(StoreUserRequest $request, User $user): RedirectResponse
    {
        $user->update($this->payload($request, false));

        return redirect()->route('admin.users.index')->with('status', 'Usuario actualizado.');
    }

    /**
     * @return array<string, mixed>
     */
    private function payload(StoreUserRequest $request, bool $creating): array
    {
        $payload = [
            'name' => $request->string('name')->toString(),
            'email' => $request->string('email')->toString(),
            'phone' => $request->input('phone'),
            'national_id' => $request->input('national_id'),
            'role_id' => $request->integer('role_id'),
            'branch_id' => $request->input('branch_id') ?: null,
            'is_active' => $request->boolean('is_active'),
        ];

        if ($creating || $request->filled('password')) {
            $payload['password'] = $request->string('password')->toString();
        }

        return $payload;
    }
}
