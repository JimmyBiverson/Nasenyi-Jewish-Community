<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\View\View;

class UserController extends Controller
{
    public function index(): View
    {
        return view('admin.users.index', [
            'users' => User::query()->orderBy('name')->paginate(25),
        ]);
    }

    public function create(): View
    {
        return view('admin.users.form', ['user' => (new User)->forceFill(['role' => 'editor'])]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validateUser($request, new User);
        $user = User::query()->make();
        $user->forceFill($data)->save();

        return to_route('admin.users.edit', $user)->with('status', 'Account created.');
    }

    public function edit(User $user): View
    {
        return view('admin.users.form', compact('user'));
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $data = $this->validateUser($request, $user);
        if (! filled($data['password'] ?? null)) {
            unset($data['password']);
        }

        DB::transaction(function () use ($request, $user, $data): void {
            $currentUser = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            abort_unless($currentUser->role === 'admin', 403);
            abort_if($currentUser->is($user) && $data['role'] !== 'admin', 403);
            $user->forceFill($data)->save();
        }, 3);

        return to_route('admin.users.edit', $user)->with('status', 'Account saved.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        DB::transaction(function () use ($request, $user): void {
            $currentUser = User::query()->lockForUpdate()->findOrFail($request->user()->id);
            abort_unless($currentUser->role === 'admin', 403);
            abort_if($currentUser->is($user), 403);
            $user->delete();
        }, 3);

        return to_route('admin.users.index')->with('status', 'Account deleted.');
    }

    /** @return array{name: string, email: string, role: string, password?: string} */
    private function validateUser(Request $request, User $user): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user)],
            'role' => ['required', Rule::in(['admin', 'editor', 'member'])],
            'password' => [$user->exists ? 'nullable' : 'required', 'string', 'confirmed', 'max:72', Password::min(12)],
        ]);
    }
}
