<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AdminActivityLog;
use App\Models\User;
use App\Support\AdminBesarCache;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AdminUserController extends Controller
{
    public function store(Request $request): RedirectResponse
    {
        $request->merge([
            'role' => strtolower((string) $request->input('role')),
        ]);

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_ADMIN_BESAR])],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $newUser = User::create([
            'name' => $payload['name'],
            'email' => $payload['email'],
            'role' => $payload['role'],
            'is_active' => (bool) ($payload['is_active'] ?? true),
            'password' => $payload['password'],
        ]);

        AdminActivityLog::create([
            'actor_user_id' => $request->user()->id,
            'action' => 'user_created',
            'subject_type' => User::class,
            'subject_id' => $newUser->id,
            'title' => 'Tambah User',
            'description' => 'Menambahkan akun user baru dari modul user admin.',
            'meta' => [
                'name' => $payload['name'],
                'email' => $payload['email'],
                'role' => $payload['role'],
            ],
        ]);

        AdminBesarCache::forgetToday();

        return redirect()->to(url('/admin/admin-module?type=users'))->with('success', 'Akun user berhasil ditambahkan.');
    }

    public function update(Request $request, User $user): RedirectResponse
    {
        $request->merge([
            'role' => strtolower((string) $request->input('role')),
        ]);

        $payload = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', Rule::in([User::ROLE_ADMIN, User::ROLE_ADMIN_BESAR])],
            'is_active' => ['nullable', 'boolean'],
            'password' => ['nullable', 'string', 'min:8', 'confirmed'],
        ]);

        $user->name = $payload['name'];
        $user->email = $payload['email'];
        $user->role = $payload['role'];
        $user->is_active = (bool) ($payload['is_active'] ?? false);

        if (!empty($payload['password'])) {
            $user->password = $payload['password'];
        }

        $user->save();

        AdminActivityLog::create([
            'actor_user_id' => $request->user()->id,
            'action' => 'user_updated',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'title' => 'Edit User',
            'description' => 'Memperbarui data user dari modul user admin.',
            'meta' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);

        AdminBesarCache::forgetToday();

        return redirect()->to(url('/admin/admin-module?type=users'))->with('success', 'Akun user berhasil diperbarui.');
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        if ((int) $request->user()->id === (int) $user->id) {
            return back()->withErrors(['users' => 'Akun yang sedang login tidak bisa dihapus.']);
        }

        $user->delete();

        AdminActivityLog::create([
            'actor_user_id' => $request->user()->id,
            'action' => 'user_deleted',
            'subject_type' => User::class,
            'subject_id' => $user->id,
            'title' => 'Hapus User',
            'description' => 'Menghapus akun user dari modul user admin.',
            'meta' => [
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ],
        ]);

        AdminBesarCache::forgetToday();

        return redirect()->to(url('/admin/admin-module?type=users'))->with('success', 'Akun user berhasil dihapus.');
    }
}
