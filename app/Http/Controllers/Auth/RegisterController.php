<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\Role;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class RegisterController extends Controller
{
    public function create(Request $request): View
    {
        $next = $request->string('next')->toString();

        if ($next !== '' && str_starts_with($next, '/') && ! str_starts_with($next, '//') && ! str_contains($next, '\\')) {
            redirect()->setIntendedUrl(url($next));
        }

        return view('auth.register', ['next' => $next]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'email' => ['required', 'email', 'max:160', 'unique:users,email'],
            'phone' => ['required', 'string', 'max:40'],
            'national_id' => ['required', 'string', 'max:20'],
            'password' => ['required', 'confirmed', Password::min(8)],
        ], [
            'national_id.required' => 'La cédula es obligatoria.',
            'phone.required' => 'El teléfono es obligatorio.',
        ]);

        $role = Role::query()->where('slug', 'cliente')->firstOrFail();

        $user = User::query()->create([
            'name' => $data['name'],
            'email' => $data['email'],
            'phone' => $data['phone'],
            'national_id' => $data['national_id'],
            'password' => $data['password'],
            'role_id' => $role->id,
        ]);

        Auth::login($user);

        return redirect()->route('client.applications.index');
    }
}
