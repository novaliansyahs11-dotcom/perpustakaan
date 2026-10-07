<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class ProfileController extends Controller
{
    public function index()
    {
        $user = Auth::user();
        // Jika member, sertakan riwayat pinjamannya
        $loans = $user->loans()->with('bookCopy.book')->latest()->paginate(5);
        return view('profile.index', compact('user', 'loans'));
    }

    public function update(Request $request)
    {
        $user = Auth::user();
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'          => ['required', 'string', 'max:20'],
            'id_card_type'   => ['required', 'in:ktm,ktp,sim'],
            'id_card_number' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($user->id)],
        ]);

        $user->update($validated);
        return back()->with('success', 'Profil berhasil diperbarui.');
    }

    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required', 'current_password'],
            'password'         => ['required', 'min:6', 'confirmed'],
        ]);

        Auth::user()->update([
            'password' => Hash::make($request->password)
        ]);

        return back()->with('success', 'Kata sandi berhasil diganti.');
    }
}