<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberPortalController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\Route;

// ==========================================
// PUBLIC ROUTES (Bisa diakses tanpa login)
// ==========================================

// Halaman Utama & Katalog Publik (Memakai view member/browse)
Route::get('/', [DashboardController::class, 'browseBooks'])->name('home');
Route::get('/katalog', [DashboardController::class, 'browseBooks'])->name('catalog.public');

// Detail Buku Publik (Memakai view member/show_book)
Route::get('/katalog/{book}', [MemberPortalController::class, 'showBook'])->name('catalog.show');

// Guest Routes (Hanya untuk pengguna yang belum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);

    // ROUTE LUPA & RESET PASSWORD (Natif Laravel)
    Route::get('/forgot-password', function () {
        return view('auth.forgot-password');
    })->name('password.request');

    Route::post('/forgot-password', function (Request $request) {
        $request->validate([
            'email' => 'required|email'
        ], [
            'email.required' => 'Alamat email wajib diisi.',
            'email.email'    => 'Format email tidak valid.'
        ]);

        $status = Password::sendResetLink($request->only('email'));

        if ($status === Password::RESET_LINK_SENT) {
            return back()->with('status', 'Link reset kata sandi telah dikirim ke email Anda (cek inbox/log).');
        }

        return back()->withErrors(['email' => 'Alamat email tersebut tidak terdaftar di sistem kami.']);
    })->name('password.email');

    // Form Input Password Baru (Wajib ada agar sendResetLink tidak error)
    Route::get('/reset-password/{token}', function (string $token) {
        return view('auth.reset-password', ['token' => $token]);
    })->name('password.reset');

    // Proses Simpan Password Baru
    Route::post('/reset-password', function (Request $request) {
        $request->validate([
            'token'    => 'required',
            'email'    => 'required|email',
            'password' => 'required|min:6|confirmed',
        ], [
            'password.required'  => 'Kata sandi baru wajib diisi.',
            'password.min'       => 'Kata sandi minimal 6 karakter.',
            'password.confirmed' => 'Konfirmasi kata sandi tidak cocok.',
        ]);

        $status = Password::reset(
            $request->only('email', 'password', 'password_confirmation', 'token'),
            function ($user, string $password) {
                $user->forceFill([
                    'password' => Hash::make($password)
                ])->save();
            }
        );

        return $status === Password::PASSWORD_RESET
            ? redirect()->route('login')->with('success', 'Kata sandi berhasil diperbarui! Silakan login.')
            : back()->withErrors(['email' => 'Gagal memperbarui kata sandi. Token tidak valid atau kadaluarsa.']);
    })->name('password.update');
});

// ==========================================
// AUTHENTICATED BASIC (Diakses Semua User Terlogin)
// ==========================================
Route::middleware('auth')->group(function () {
    // Tombol Logout harus selalu bisa diakses meskipun email belum diverifikasi
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

    // VERIFIKASI EMAIL (EMAIL VERIFICATION FLOW)
    // 1. Tampilan Instruksi Cek Inbox Email
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    // 2. Proses Klik Link Verifikasi dari Email
    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('dashboard')->with('success', 'Email Anda berhasil diverifikasi!');
    })->middleware(['signed'])->name('verification.verify');

    // 3. Kirim Ulang Email Verifikasi
    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Link verifikasi baru telah dikirim ke email Anda!');
    })->middleware(['throttle:6,1'])->name('verification.send');
});

// ==========================================
// VERIFIED ROUTES (Wajib Login & Email Terverifikasi)
// ==========================================
Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modul Kategori Buku
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Modul Data Buku & Eksemplar
    Route::resource('books', BookController::class);
    Route::post('/books/{book}/add-copy', [BookController::class, 'addCopy'])->name('books.add-copy');

    // Modul Sirkulasi Peminjaman & Pengembalian
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::post('/loans/{loan}/return', [LoanController::class, 'returnBook'])->name('loans.return');
    Route::get('/loans-report', [LoanController::class, 'report'])->name('loans.report');
    Route::get('/loans/{loan}/fine-receipt', [LoanController::class, 'printFineReceipt'])->name('loans.fine-receipt');

    // Scan QR Token Kasir
    Route::get('/loans/scan-token', [LoanController::class, 'scanTokenView'])->name('loans.scan-view');
    Route::post('/loans/scan-token', [LoanController::class, 'processScannedToken'])->name('loans.scan-token');

    // Modul Manajemen Anggota
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
    Route::get('/members/{member}/print', [MemberController::class, 'printReport'])->name('members.print');
    Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
    Route::patch('/members/{member}/toggle', [MemberController::class, 'toggleStatus'])->name('members.toggle');
    Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');

    // Modul Profil Pengguna
    Route::get('/profile', [MemberPortalController::class, 'profile'])->name('member.profile');
    Route::put('/profile', [MemberPortalController::class, 'updateProfile'])->name('member.profile.update');
    Route::put('/profile/password', [MemberPortalController::class, 'updatePassword'])->name('member.profile.password');

    // Area Member (Logged-In Area)
    Route::get('/browse', [DashboardController::class, 'browseBooks'])->name('member.browse');
    Route::get('/my-loans', [DashboardController::class, 'myLoans'])->name('member.loans');
    Route::get('/my-loans/print', [DashboardController::class, 'printMyLoans'])->name('member.loans.print');
    Route::get('/book/{book}', [MemberPortalController::class, 'showBook'])->name('member.books.show');

    // Generate Tiket QR Pinjam Mandiri
    Route::post('/member/loan-token/{bookCopyId}', [MemberPortalController::class, 'generateToken'])->name('member.loan.token');
    Route::get('/member/ticket/{id}', [MemberPortalController::class, 'showTicket'])->name('member.ticket');
});