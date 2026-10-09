<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

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

            // Tentukan halaman default berdasarkan role pengguna
            $defaultUrl = Auth::user()->role === 'admin' 
                ? route('dashboard') 
                : (Route::has('member.browse') ? route('member.browse') : route('dashboard'));

            // Redirect ke halaman yang dituju sebelumnya (intended) atau ke default URL
            return redirect()->intended($defaultUrl);
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
        // 1. Normalisasi / Konversi Nomor Telepon (Ubah 08.../0... menjadi 628...)
        if ($request->has('phone')) {
            $cleanPhone = preg_replace('/[^0-9]/', '', $request->phone);
            if (str_starts_with($cleanPhone, '08')) {
                $cleanPhone = '628' . substr($cleanPhone, 2);
            } elseif (str_starts_with($cleanPhone, '0')) {
                $cleanPhone = '62' . substr($cleanPhone, 1);
            }
            $request->merge(['phone' => $cleanPhone]);
        }

        // 2. Custom Validation Rule untuk Nomor Identitas berdasarkan jenis kartu
        $idCardRules = ['required', 'regex:/^[0-9]+$/', 'unique:users,id_card_number'];

        if ($request->id_card_type === 'ktp') {
            $idCardRules[] = 'digits:16'; // KTP (NIK) wajib 16 digit angka
        } elseif ($request->id_card_type === 'sim') {
            $idCardRules[] = 'digits:12'; // SIM wajib 12 digit angka
        } elseif ($request->id_card_type === 'ktm') {
            $idCardRules[] = 'digits_between:10,14'; // KTM (NIM) antara 10 - 14 digit angka
        }

        $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'string', 'email', 'max:255', 'unique:users'],
            'phone'          => ['required', 'regex:/^(628)[0-9]{8,11}$/'], // Format 628 dengan total 11-14 digit angka
            'id_card_type'   => ['required', 'in:ktm,ktp,sim'],
            'id_card_number' => $idCardRules,
            'password'       => ['required', 'string', 'min:6', 'confirmed'],
        ], [
            'name.required'                 => 'Nama lengkap wajib diisi.',
            'email.required'                => 'Alamat email wajib diisi.',
            'email.email'                   => 'Format email tidak valid.',
            'email.unique'                  => 'Email sudah terdaftar.',
            
            // Pesan error validasi nomor HP
            'phone.required'                => 'Nomor HP wajib diisi.',
            'phone.regex'                   => 'Nomor HP harus berupa format Indonesia valid (contoh: 08123456789 atau 628123456789).',
            
            'id_card_type.required'         => 'Jenis identitas wajib dipilih.',
            'id_card_type.in'               => 'Pilihan identitas harus berupa KTM, KTP, atau SIM.',
            
            // Pesan error validasi nomor identitas
            'id_card_number.required'       => 'Nomor identitas wajib diisi.',
            'id_card_number.regex'          => 'Nomor identitas hanya boleh berisi angka.',
            'id_card_number.digits'         => 'Nomor identitas tidak sesuai jumlah digit resmi (KTP: 16 digit, SIM: 12 digit).',
            'id_card_number.digits_between' => 'Nomor KTM (NIM) harus berisi antara 10 hingga 14 digit angka.',
            'id_card_number.unique'         => 'Nomor identitas ini sudah terdaftar di sistem.',
            
            'password.required'             => 'Kata sandi wajib diisi.',
            'password.min'                  => 'Kata sandi minimal 6 karakter.',
            'password.confirmed'            => 'Konfirmasi kata sandi tidak cocok.',
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

        $defaultUrl = Route::has('member.browse') ? route('member.browse') : route('dashboard');

        return redirect()->intended($defaultUrl)->with('success', 'Pendaftaran berhasil! Selamat datang di Perpustakaan Online.');
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();
        
        // Setelah logout, arahkan kembali ke katalog publik atau halaman login
        return Route::has('catalog.public') 
            ? redirect()->route('catalog.public') 
            : redirect()->route('login');
    }
}