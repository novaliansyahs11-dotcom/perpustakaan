<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\LoanToken;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MemberPortalController extends Controller
{
    // Detail Buku untuk Member (Hanya Lihat Informasi & Ketersediaan Stok)
    public function showBook(Book $book)
    {
        $book->load(['category', 'copies']);
        $availableCopiesCount = $book->copies->where('status', 'available')->count();
        $borrowedCopiesCount = $book->copies->where('status', 'borrowed')->count();

        return view('member.show_book', compact('book', 'availableCopiesCount', 'borrowedCopiesCount'));
    }

    // Menampilkan Daftar Pinjaman & Tiket QR Aktif Member
    public function myLoans()
    {
        $user = Auth::user();

        // 1. Ambil tiket QR mandiri yang masih pending dan belum kedaluwarsa (60 menit)
        $activeTokens = LoanToken::with(['bookCopy.book'])
            ->where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', Carbon::now())
            ->latest()
            ->get();

        // 2. Ambil data riwayat pinjaman dengan pagination
        $loans = Loan::with(['bookCopy.book'])
            ->where('user_id', $user->id)
            ->latest()
            ->paginate(10);

        return view('member.loans', compact('activeTokens', 'loans'));
    }

    // Halaman Profil Anggota
    public function profile()
    {
        $user = Auth::user();

        // Pinjaman yang sedang aktif berjalan
        $activeLoans = Loan::with('bookCopy.book')
            ->where('user_id', $user->id)
            ->where('status', 'borrowed')
            ->latest()
            ->get();

        // Histori buku yang sudah dikembalikan
        $loanHistory = Loan::with('bookCopy.book')
            ->where('user_id', $user->id)
            ->where('status', 'returned')
            ->latest()
            ->paginate(5);

        $totalBorrowed = Loan::where('user_id', $user->id)->count();
        $totalFines = Loan::where('user_id', $user->id)->sum('fine_amount');

        return view('member.profile', compact('user', 'activeLoans', 'loanHistory', 'totalBorrowed', 'totalFines'));
    }

    // Perbarui Data Diri Anggota
    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        // 1. Normalisasi Otomatis Format Nomor Telepon (Konversi ke 62...)
        $phone = $request->phone;
        if ($phone) {
            $phone = preg_replace('/[^0-9]/', '', $phone);
            if (str_starts_with($phone, '0')) {
                $phone = '62' . substr($phone, 1);
            } elseif (str_starts_with($phone, '8')) {
                $phone = '62' . $phone;
            }
        }
        $request->merge(['phone' => $phone]);

        // 2. Normalisasi Otomatis Nomor Identitas (Hapus karakter non-angka)
        if ($request->id_card_number) {
            $request->merge([
                'id_card_number' => preg_replace('/[^0-9]/', '', $request->id_card_number)
            ]);
        }

        // 3. Aturan Panjang Digit Dinamis Berdasarkan Jenis Identitas
        $idRules = match ($request->id_card_type) {
            'ktm' => 'digits_between:11,13',
            'ktp' => 'digits:16',
            'sim' => 'digits_between:12,14',
            default => 'digits_between:10,20',
        };

        $idMsg = match ($request->id_card_type) {
            'ktm' => 'Nomor KTM (NIM) harus berupa angka 11 sampai 13 digit.',
            'ktp' => 'Nomor KTP (NIK) harus berupa angka tepat 16 digit.',
            'sim' => 'Nomor SIM harus berupa angka 12 sampai 14 digit.',
            default => 'Nomor identitas tidak valid.',
        };

        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'phone'          => ['required', 'numeric', 'digits_between:10,15', Rule::unique('users')->ignore($user->id)],
            'id_card_type'   => ['required', 'in:ktm,ktp,sim'],
            'id_card_number' => ['required', 'numeric', $idRules, Rule::unique('users')->ignore($user->id)],
        ], [
            'name.required'           => 'Nama lengkap wajib diisi.',
            'email.required'          => 'Alamat email wajib diisi.',
            'email.unique'            => 'Email sudah digunakan oleh akun lain.',
            'phone.required'          => 'Nomor handphone wajib diisi.',
            'phone.numeric'           => 'Nomor handphone hanya boleh berisi angka.',
            'phone.digits_between'    => 'Nomor handphone minimal 10 digit dan maksimal 15 digit.',
            'phone.unique'            => 'Nomor handphone sudah terdaftar di akun lain.',
            'id_card_type.required'   => 'Jenis kartu identitas wajib dipilih.',
            'id_card_number.required' => 'Nomor identitas fisik wajib diisi.',
            'id_card_number.numeric'  => 'Nomor identitas fisik hanya boleh berisi angka.',
            'id_card_number.digits'   => $idMsg,
            'id_card_number.digits_between' => $idMsg,
            'id_card_number.unique'   => 'Nomor identitas sudah terdaftar di sistem.',
        ]);

        $user->update($validated);

        return back()->with('success', 'Data profil Anda berhasil diperbarui!');
    }

    // Perbarui Password Profil
    public function updatePassword(Request $request)
    {
        $request->validate([
            'current_password' => ['required'],
            'password'         => ['required', 'min:6', 'confirmed'],
        ], [
            'current_password.required' => 'Kata sandi saat ini wajib diisi.',
            'password.required'         => 'Kata sandi baru wajib diisi.',
            'password.min'              => 'Kata sandi baru minimal 6 karakter.',
            'password.confirmed'        => 'Konfirmasi kata sandi baru tidak cocok.',
        ]);

        $user = Auth::user();

        if (!Hash::check($request->current_password, $user->password)) {
            return back()->with('error', 'Kata sandi saat ini tidak sesuai!');
        }

        $user->update([
            'password' => Hash::make($request->password),
        ]);

        return back()->with('success', 'Kata sandi Anda berhasil diperbarui!');
    }

    // Pembuatan Tiket Pinjam Mandiri (Token Barcode Berlaku 60 Menit)
    public function generateToken(Request $request, $bookCopyId)
    {
        $user = Auth::user();

        // 1. Validasi Kuota: Member tidak boleh meminjam jika masih ada buku berstatus dipinjam
        $hasActiveLoan = Loan::where('user_id', $user->id)
                           ->where('status', 'borrowed')
                           ->exists();
        if ($hasActiveLoan) {
            return back()->with('error', 'Gagal membuat tiket! Anda masih memiliki pinjaman buku aktif (Batas kuota maksimal 1 buku).');
        }

        // 2. Cek apakah member sudah memiliki tiket yang masih aktif (belum expired)
        $existingActiveToken = LoanToken::where('user_id', $user->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', Carbon::now())
            ->first();

        if ($existingActiveToken) {
            return redirect()->route('member.ticket', $existingActiveToken->id)
                ->with('warning', 'Anda masih memiliki tiket aktif yang belum dipindai petugas.');
        }

        // 3. Kunci eksemplar fisik dan generate token barcode dalam DB Transaction
        return DB::transaction(function () use ($user, $bookCopyId) {
            $copy = BookCopy::lockForUpdate()->findOrFail($bookCopyId);

            if ($copy->status !== 'available') {
                return back()->with('error', 'Maaf, buku ini baru saja diambil atau dipesan oleh anggota lain.');
            }

            // Ubah status eksemplar sementara agar tidak diambil peminjam lain di rak
            $copy->update(['status' => 'reserved_temp']);

            // Buat token barcode unik dengan masa aktif tepat 60 menit
            $token = LoanToken::create([
                'token_code'   => 'TKN-' . strtoupper(Str::random(8)),
                'user_id'      => $user->id,
                'book_copy_id' => $copy->id,
                'expires_at'   => Carbon::now()->addHour(),
                'status'       => 'pending',
            ]);

            return redirect()->route('member.ticket', $token->id)
                ->with('success', 'Tiket Barcode berhasil dibuat! Segera serahkan ke petugas dalam waktu 60 menit.');
        });
    }

    // Menampilkan Layar Tiket QR / Barcode Mandiri Mahasiswa
    public function showTicket($id)
    {
        $token = LoanToken::with(['user', 'bookCopy.book'])->findOrFail($id);

        // Pastikan hanya pemilik tiket atau admin yang dapat melihat tiket ini
        if (Auth::user()->role !== 'admin' && Auth::id() !== $token->user_id) {
            abort(403, 'Akses ditolak.');
        }

        // Cek jika waktu 60 menit sudah lewat dan status masih pending
        if ($token->status === 'pending' && Carbon::now()->gt($token->expires_at)) {
            $token->update(['status' => 'expired']);
            $token->bookCopy->update(['status' => 'available']);
        }

        return view('member.ticket', compact('token'));
    }
}