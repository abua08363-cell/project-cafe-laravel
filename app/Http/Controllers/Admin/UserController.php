<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class UserController extends Controller
{
    public function index()
    {
        return view('admin.users.index', [
            'users' => User::latest()->get(),
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

        $data['name'] = $request->username;

        $data['password'] = Hash::make($data['password']);

        if ($request->hasFile('photo')) {
            $name = $request->file('photo')->hashName();
            $request->file('photo')->move(public_path('uploads/users'), $name);
            $data['photo'] = $name;
        }

        User::create($data);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil ditambahkan.');
    }

    public function edit(User $user)
    {
        $this->authorizeStaff($user);

        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $this->authorizeStaff($user);

        $data = $request->validate([
            'username' => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'email'    => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role'     => ['required', 'string', 'max:50'],
            'password' => ['nullable', 'string', 'min:6'],
            'photo'    => ['nullable', 'image', 'max:2048'],
        ]);

        if (!empty($data['password'])) {
            $data['password'] = Hash::make($data['password']);
        } else {
            unset($data['password']);
        }

        if ($request->hasFile('photo')) {
            $this->deletePhoto($user);
            $name = $request->file('photo')->hashName();
            $request->file('photo')->move(public_path('uploads/users'), $name);
            $data['photo'] = $name;
        }

        $user->update($data);

        return redirect()->route('admin.users.index')->with('success', 'User berhasil diperbarui.');
    }

    public function destroy(User $user)
    {
        $this->authorizeStaff($user);

        if ($user->id === auth()->id()) {
            return back()->with('error', 'Kamu tidak bisa menghapus akunmu sendiri.');
        }

        $this->deletePhoto($user);
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', 'User berhasil dihapus.');
    }

    /** Hanya owner (role "staff") yang boleh mengubah/menghapus akun owner lain. */
    private function authorizeStaff(User $target): void
    {
        $isOwner = session('role') === 'staff';

        if ($target->role === 'staff' && !$isOwner) {
            abort(403, 'Khusus Owner.');
        }
    }

    private function deletePhoto(User $user): void
    {
        if ($user->photo && file_exists(public_path('uploads/users/' . $user->photo))) {
            @unlink(public_path('uploads/users/' . $user->photo));
        }
    }
}