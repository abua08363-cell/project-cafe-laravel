<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function index(): View
    {
        return view('admin.settings.index', [
            'settings' => Setting::query()->pluck('value', 'key')->all(),
            'user'     => Auth::user(),
        ]);
    }

    public function update(Request $request): RedirectResponse
    {
        $user = Auth::user();

        $validated = $request->validate([
            'store_status'   => ['required', 'in:open,closed'],
            'opening_hours'  => ['nullable', 'string', 'max:100'],
            'tax_percentage' => ['nullable', 'numeric', 'min:0', 'max:100'],
            'store_name'     => ['required', 'string', 'max:150'],
            'store_tagline'  => ['nullable', 'string', 'max:255'],
            'phone_number'   => ['nullable', 'string', 'max:50'],
            'contact_email'  => ['nullable', 'email', 'max:255'],
            'store_address'  => ['nullable', 'string', 'max:500'],

            'pay_qris'       => ['nullable', 'boolean'],
            'pay_cash'       => ['nullable', 'boolean'],
            'pay_transfer'   => ['nullable', 'boolean'],

            'username'       => ['required', 'string', 'max:100', Rule::unique('users', 'username')->ignore($user->id)],
            'email'          => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'password'       => ['nullable', 'string', 'min:6'],
        ]);

        // ===== Simpan pengaturan kedai =====
        $textKeys = [
            'store_status', 'opening_hours', 'tax_percentage', 'store_name',
            'store_tagline', 'phone_number', 'contact_email', 'store_address',
        ];

        foreach ($textKeys as $key) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $validated[$key] ?? '']
            );
        }

        // ===== Metode pembayaran (selalu simpan 1 / 0) =====
        foreach (['pay_qris', 'pay_cash', 'pay_transfer'] as $key) {
            Setting::query()->updateOrCreate(
                ['key' => $key],
                ['value' => $request->boolean($key) ? 1 : 0]
            );
        }

        // ===== Update akun admin di tabel users =====
        $user->username = $validated['username'];
        $user->email    = $validated['email'];

        if (!empty($validated['password'])) {
            $user->password = Hash::make($validated['password']);
        }

        $user->save();

        return redirect()
            ->route('admin.settings.index')
            ->with('success', 'Semua pengaturan kedai berhasil diperbarui.');
    }
}