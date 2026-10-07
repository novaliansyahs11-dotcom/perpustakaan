<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\LoanController;
use App\Http\Controllers\MemberController;
use App\Http\Controllers\MemberPortalController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect()->route('login');
});

// Guest Routes (Hanya dapat diakses sebelum login)
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('/login', [AuthController::class, 'login']);

    // Rute Registrasi Member Baru
    Route::get('/register', [AuthController::class, 'showRegister'])->name('register');
    Route::post('/register', [AuthController::class, 'register']);
});

// Authenticated Routes (Wajib Login)
Route::middleware('auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout'])->name('logout');
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // Modul Kategori Buku (Lengkap dengan Edit & Update)
    Route::get('/categories', [CategoryController::class, 'index'])->name('categories.index');
    Route::post('/categories', [CategoryController::class, 'store'])->name('categories.store');
    Route::put('/categories/{category}', [CategoryController::class, 'update'])->name('categories.update');
    Route::delete('/categories/{category}', [CategoryController::class, 'destroy'])->name('categories.destroy');

    // Modul Data Buku & Eksemplar
    Route::resource('books', BookController::class);
    Route::post('/books/{book}/add-copy', [BookController::class, 'addCopy'])->name('books.add-copy');

    // Modul Sirkulasi Peminjaman, Pengembalian & Cetak Slip Denda Per Transaksi
    Route::get('/loans', [LoanController::class, 'index'])->name('loans.index');
    Route::get('/loans/create', [LoanController::class, 'create'])->name('loans.create');
    Route::post('/loans', [LoanController::class, 'store'])->name('loans.store');
    Route::post('/loans/{loan}/return', [LoanController::class, 'returnBook'])->name('loans.return');
    Route::get('/loans-report', [LoanController::class, 'report'])->name('loans.report');
    Route::get('/loans/{loan}/fine-receipt', [LoanController::class, 'printFineReceipt'])->name('loans.fine-receipt');

    // Verifikasi & Scan Tiket Barcode Digital oleh Petugas Meja Sirkulasi
    Route::get('/loans/scan-token', [LoanController::class, 'scanTokenView'])->name('loans.scan-view');
    Route::post('/loans/scan-token', [LoanController::class, 'processScannedToken'])->name('loans.scan-token');

    // Modul Manajemen Anggota (Lengkap: Index, Store, Show, Print Report, Edit, Update, Toggle Status, Destroy)
    Route::get('/members', [MemberController::class, 'index'])->name('members.index');
    Route::post('/members', [MemberController::class, 'store'])->name('members.store');
    Route::get('/members/{member}', [MemberController::class, 'show'])->name('members.show');
    Route::get('/members/{member}/print', [MemberController::class, 'printReport'])->name('members.print');
    Route::get('/members/{member}/edit', [MemberController::class, 'edit'])->name('members.edit');
    Route::put('/members/{member}', [MemberController::class, 'update'])->name('members.update');
    Route::patch('/members/{member}/toggle', [MemberController::class, 'toggleStatus'])->name('members.toggle');
    Route::delete('/members/{member}', [MemberController::class, 'destroy'])->name('members.destroy');

    // Modul Profil Pengguna (Admin & Member)
    Route::get('/profile', [MemberPortalController::class, 'profile'])->name('member.profile');
    Route::put('/profile', [MemberPortalController::class, 'updateProfile'])->name('member.profile.update');
    Route::put('/profile/password', [MemberPortalController::class, 'updatePassword'])->name('member.profile.password');

    // Modul Area Member (Katalog, Detail Buku, Riwayat Mandiri & Cetak Report Mandiri)
    Route::get('/browse', [DashboardController::class, 'browseBooks'])->name('member.browse');
    Route::get('/my-loans', [DashboardController::class, 'myLoans'])->name('member.loans');
    Route::get('/my-loans/print', [DashboardController::class, 'printMyLoans'])->name('member.loans.print');
    Route::get('/book/{book}', [MemberPortalController::class, 'showBook'])->name('member.books.show');

    // Fitur Tiket QR Pinjam Mandiri (Token 60 Menit)
    Route::post('/member/loan-token/{bookCopyId}', [MemberPortalController::class, 'generateToken'])->name('member.loan.token');
    Route::get('/member/ticket/{id}', [MemberPortalController::class, 'showTicket'])->name('member.ticket');
});