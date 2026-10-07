<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function showLogin()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required'],
        ]);

        if (Auth::attempt($credentials)) {
            $request->session()->regenerate();

            if (!Auth::user()->is_active) {
                Auth::logout();
                $request->session()->invalidate();
                $request->session()->regenerateToken();
                return back()->withErrors(['email' => 'Akun Anda dinonaktifkan oleh administrator.']);
            }

            return redirect()->intended(route('dashboard'));
        }

        return back()->withErrors([
            'email' => 'Email atau password yang dimasukkan salah.',
        ])->onlyInput('email');
    }

    public function showRegister()
    {
        if (Auth::check()) {
            return redirect()->route('dashboard');
        }
        return view('auth.register');
    }

    public function register(Request $request)
    {
        $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'          => ['required', 'string', 'max:20'],
            'id_card_type'   => ['required', 'in:ktm,ktp,sim'],
            'id_card_number' => ['required', 'string', 'max:50', 'unique:users,id_card_number'],
            'password'       => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required'           => 'Nama lengkap wajib diisi.',
            'email.required'          => 'Alamat email wajib diisi.',
            'email.email'             => 'Format email tidak valid.',
            'email.unique'            => 'Email sudah terdaftar.',
            'phone.required'          => 'Nomor HP wajib diisi.',
            'id_card_type.required'   => 'Jenis identitas wajib dipilih.',
            'id_card_type.in'         => 'Pilihan identitas harus berupa KTM, KTP, atau SIM.',
            'id_card_number.required' => 'Nomor identitas (KTM/KTP/SIM) wajib diisi.',
            'id_card_number.unique'   => 'Nomor identitas ini sudah terdaftar di sistem.',
            'password.required'       => 'Kata sandi wajib diisi.',
            'password.min'            => 'Kata sandi minimal 6 karakter.',
            'password.confirmed'      => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $user = User::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'phone'          => $request->phone,
            'id_card_type'   => $request->id_card_type,
            'id_card_number' => $request->id_card_number,
            'password'       => Hash::make($request->password),
            'role'           => 'member',
            'is_active'      => true,
        ]);

        Auth::login($user);
        $request->session()->regenerate();

        return redirect()->route('dashboard')->with('success', 'Pendaftaran berhasil! Selamat datang di Perpustakaan Online.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        return redirect()->route('login');
    }
}