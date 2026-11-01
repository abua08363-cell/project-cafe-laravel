<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    private const OWNER_ROLE = 'staff';

    public function index()
    {
        return view('admin.users.index', [
            'users'   => User::latest()->get(),
            'isOwner' => $this->isOwner(),
        ]);
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'username' => ['required', 'string', 'max:100', 'unique:users,username'],
            'email'    => ['required', 'email', 'max:255', 'unique:users,email'],
            'role'     => ['required', 'string', 'max:50'],
            'password' => ['required', 'string', 'min:6'],
            'photo'    => ['nullable', 'image', 'max:2048'],
        ]);

        // Hanya Owner yang boleh membuat akun Owner
        if ($data['role'] === self::OWNER_ROLE && !$this->isOwner()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Hanya Owner yang dapat membuat akun Owner.');
        }

        $data['name']     = $data['username'];
        $data['password'] = Hash::make($data['password']);

        if ($request->hasFile('photo')) {
            $data['photo'] = $this->storePhoto($request);
        } else {
            unset($data['photo']);
        }

        User::create($data);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        if ($denied = $this->denyIfNotOwner($user)) {
            return $denied;
        }

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        if ($denied = $this->denyIfNotOwner($user)) {
            return $denied;
        }

        $data = $request->validate([
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'     => ['required', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:6'],
            'photo'    => ['nullable', 'image', 'max:2048'],
        ]);

        // Akun sendiri: role tidak boleh diubah (cegah kehilangan akses / eskalasi)
        if ($this->isSelf($user)) {
            $data['role'] = $user->role;
        }

        // Hanya Owner yang boleh memberi role Owner
        if ($data['role'] === self::OWNER_ROLE && !$this->isOwner()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Hanya Owner yang dapat memberi role Owner.');
        }

        $data['name'] = $data['username'];

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('photo')) {
            $this->deletePhoto($user);
            $data['photo'] = $this->storePhoto($request);
        } else {
            unset($data['photo']);
        }

        $user->update($data);

        // Sinkronkan session kalau user mengedit akunnya sendiri
        if ($this->isSelf($user)) {
            session([
                'role'     => $user->role,
                'username' => $user->username ?? $user->name,
            ]);
        }

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        // 1) Tidak boleh menghapus akun sendiri (dicek paling awal)
        if ($this->isSelf($user)) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Kamu tidak bisa menghapus akunmu sendiri yang sedang login.');
        }

        // 2) Akun Owner hanya bisa dihapus Owner
        if ($denied = $this->denyIfNotOwner($user)) {
            return $denied;
        }

        // 3) Hapus; tangkap error relasi/foreign key agar tidak muncul halaman error
        try {
            $user->delete();
        } catch (QueryException $e) {
            return redirect()->route('admin.users.index')
                ->with('error', 'User tidak bisa dihapus karena masih memiliki data terkait.');
        }

        $this->deletePhoto($user);

        return redirect()->route('admin.users.index')
            ->with('success', 'User berhasil dihapus.');
    }

    /* ===================== Helper ===================== */

    private function isOwner(): bool
    {
        $role = Auth::user()->role ?? session('role');

        return $role === self::OWNER_ROLE;
    }

    private function isSelf(User $user): bool
    {
        return (int) $user->id === (int) Auth::id();
    }

    /**
     * Akun Owner hanya boleh diubah/dihapus oleh Owner.
     * Mengembalikan redirect (bukan abort) supaya tidak muncul halaman error.
     */
    private function denyIfNotOwner(User $target): ?RedirectResponse
    {
        if ($target->role === self::OWNER_ROLE && !$this->isOwner()) {
            return redirect()->route('admin.users.index')
                ->with('error', 'Akun Owner hanya dapat dikelola oleh Owner.');
        }

        return null;
    }

    private function storePhoto(Request $request): string
    {
        $dir = public_path('uploads/users');
        File::ensureDirectoryExists($dir);

        $name = $request->file('photo')->hashName();
        $request->file('photo')->move($dir, $name);

        return $name;
    }

    private function deletePhoto(User $user): void
    {
        if ($user->photo) {
            $path = public_path('uploads/users/' . $user->photo);

            if (is_file($path)) {
                @unlink($path);
            }
        }
    }
}