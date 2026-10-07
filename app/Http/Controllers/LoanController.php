<?php

namespace App\Http\Controllers;

use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\LoanToken;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class LoanController extends Controller
{
    public function index(Request $request)
    {
        $query = Loan::with(['user', 'bookCopy.book', 'admin']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('user', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('phone', 'like', "%{$search}%");
            })->orWhereHas('bookCopy', function($q) use ($search) {
                $q->where('copy_code', 'like', "%{$search}%")
                  ->orWhereHas('book', function($qb) use ($search) {
                      $qb->where('title', 'like', "%{$search}%");
                  });
            });
        }

        $loans = $query->latest()->paginate(10)->withQueryString();
        return view('loans.index', compact('loans'));
    }

    public function create()
    {
        $members = User::where('role', 'member')->where('is_active', true)->orderBy('name')->get();
        $availableCopies = BookCopy::with('book')->where('status', 'available')->get();

        return view('loans.create', compact('members', 'availableCopies'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'user_id'      => 'required|exists:users,id',
            'book_copy_id' => 'required|exists:book_copies,id',
            'borrow_date'  => 'required|date',
            'due_date'     => 'required|date|after_or_equal:borrow_date',
        ], [
            'user_id.required'        => 'Pilih anggota peminjam.',
            'book_copy_id.required'   => 'Pilih eksemplar buku fisik.',
            'due_date.after_or_equal' => 'Tanggal jatuh tempo tidak boleh sebelum tanggal pinjam.',
        ]);

        // Cek Kuota: Member maksimal pinjam 1 buku aktif
        $hasActiveLoan = Loan::where('user_id', $request->user_id)
                             ->where('status', 'borrowed')
                             ->exists();
        if ($hasActiveLoan) {
            return back()->with('error', 'Gagal! Anggota ini masih memiliki pinjaman aktif yang belum dikembalikan (Batas kuota 1 buku).');
        }

        $copy = BookCopy::findOrFail($request->book_copy_id);
        if ($copy->status !== 'available') {
            return back()->with('error', 'Eksemplar buku ini sedang dipinjam atau tidak tersedia di rak.');
        }

        DB::transaction(function () use ($request, $copy) {
            Loan::create([
                'user_id'      => $request->user_id,
                'book_copy_id' => $request->book_copy_id,
                'admin_id'     => Auth::id(),
                'borrow_date'  => $request->borrow_date,
                'due_date'     => $request->due_date,
                'status'       => 'borrowed',
            ]);

            $copy->update(['status' => 'borrowed']);
        });

        return redirect()->route('loans.index')->with('success', 'Transaksi peminjaman berhasil dicatat!');
    }

    // Tampilkan Halaman Scanner untuk Petugas Meja Sirkulasi
    public function scanTokenView()
    {
        return view('loans.scan_token');
    }

    // Proses Tiket QR Barcode Pinjam Mandiri
    public function processScannedToken(Request $request)
    {
        $request->validate([
            'token_code' => 'required|string',
        ], [
            'token_code.required' => 'Kode token / barcode wajib diisi atau dipindai.',
        ]);

        $tokenCode = trim($request->token_code);

        $token = LoanToken::with(['user', 'bookCopy.book'])
                          ->where('token_code', $tokenCode)
                          ->first();

        if (!$token) {
            return back()->with('error', "Tiket dengan kode [{$tokenCode}] tidak ditemukan!");
        }

        // Cek jika sudah kedaluwarsa (> 60 menit)
        if ($token->status === 'expired' || Carbon::now()->gt($token->expires_at)) {
            $token->update(['status' => 'expired']);
            $token->bookCopy->update(['status' => 'available']);
            return back()->with('error', "Tiket [{$tokenCode}] sudah KEDALUWARSA (Lewat dari 60 menit)! Buku telah otomatis dikembalikan ke rak.");
        }

        // Cek jika sudah pernah diclaim
        if ($token->status === 'claimed') {
            return back()->with('warning', "Tiket [{$tokenCode}] sudah pernah diproses sebelumnya.");
        }

        // Validasi Kuota Peminjam
        $hasActiveLoan = Loan::where('user_id', $token->user_id)
                             ->where('status', 'borrowed')
                             ->exists();
        if ($hasActiveLoan) {
            return back()->with('error', "Peminjam {$token->user->name} masih memiliki pinjaman buku aktif lain yang belum dikembalikan!");
        }

        // Simpan Transaksi Resmi Peminjaman Mandiri
        DB::transaction(function () use ($token) {
            Loan::create([
                'user_id'      => $token->user_id,
                'book_copy_id' => $token->book_copy_id,
                'admin_id'     => Auth::id(),
                'borrow_date'  => Carbon::now()->toDateString(),
                'due_date'     => Carbon::now()->addDays(7)->toDateString(),
                'late_days'    => 0,
                'fine_amount'  => 0,
                'status'       => 'borrowed',
            ]);

            // Kunci status tiket dan fisik buku
            $token->update(['status' => 'claimed']);
            $token->bookCopy->update(['status' => 'borrowed']);
        });

        return redirect()->route('loans.index')->with('success', 
            "Peminjaman Mandiri Berhasil Diverifikasi! Buku '{$token->bookCopy->book->title}' ({$token->bookCopy->copy_code}) resmi diserahkan kepada {$token->user->name}."
        );
    }

    public function returnBook(Request $request, Loan $loan)
    {
        if ($loan->status === 'returned') {
            return back()->with('error', 'Buku ini sudah dikembalikan sebelumnya.');
        }

        $returnDate = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($loan->due_date)->startOfDay();
        
        $daysLate = 0;
        $fineAmount = 0;

        if ($returnDate->gt($dueDate)) {
            $daysLate = $returnDate->diffInDays($dueDate);
            $bookPrice = $loan->bookCopy->book->price ?? 0;

            if ($daysLate <= 7) {
                $fineAmount = $bookPrice * 0.10;
            } elseif ($daysLate <= 14) {
                $fineAmount = $bookPrice * 0.20;
            } else {
                $fineAmount = $bookPrice * 0.30;
            }

            // Plafon maksimal denda 100% seharga buku
            if ($fineAmount > $bookPrice) {
                $fineAmount = $bookPrice;
            }
        }

        DB::transaction(function () use ($loan, $fineAmount, $daysLate) {
            $loan->update([
                'return_date' => Carbon::now()->toDateString(),
                'status'      => 'returned',
                'late_days'   => $daysLate,
                'fine_amount' => $fineAmount,
            ]);

            $loan->bookCopy->update(['status' => 'available']);
        });

        return redirect()->route('loans.index')->with('success', 'Pengembalian buku berhasil diproses. Denda tercatat: Rp ' . number_format($fineAmount, 0, ',', '.'));
    }

    public function printFineReceipt(Loan $loan)
    {
        if (Auth::user()->role === 'member' && $loan->user_id !== Auth::id()) {
            abort(403, 'Akses tidak diizinkan.');
        }

        $loan->load(['user', 'bookCopy.book', 'admin']);

        $today = Carbon::now()->startOfDay();
        $dueDate = Carbon::parse($loan->due_date)->startOfDay();
        
        $daysLate = 0;
        $finePercentage = 0;
        $fineAmount = $loan->fine_amount;

        if ($loan->status === 'returned' && $loan->return_date) {
            $returnDate = Carbon::parse($loan->return_date)->startOfDay();
            if ($returnDate->gt($dueDate)) {
                $daysLate = $returnDate->diffInDays($dueDate);
            }
        } elseif ($loan->status === 'borrowed') {
            if ($today->gt($dueDate)) {
                $daysLate = $today->diffInDays($dueDate);
            }
        }

        $bookPrice = $loan->bookCopy->book->price ?? 0;

        if ($daysLate > 0) {
            if ($daysLate <= 7) {
                $finePercentage = 10;
            } elseif ($daysLate <= 14) {
                $finePercentage = 20;
            } else {
                $finePercentage = 30;
            }

            if ($fineAmount <= 0) {
                $fineAmount = ($bookPrice * $finePercentage) / 100;
            }

            if ($fineAmount > $bookPrice) {
                $fineAmount = $bookPrice;
            }
        }

        return view('loans.fine_receipt', compact('loan', 'daysLate', 'finePercentage', 'fineAmount', 'bookPrice'));
    }

    // Laporan Rekapitulasi Sirkulasi
    public function report(Request $request)
    {
        $query = Loan::with(['user', 'bookCopy.book', 'admin']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('borrow_date', [$request->start_date, $request->end_date]);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $loans = $query->latest()->get();
        $totalFines = $loans->sum('fine_amount');

        return view('loans.report', compact('loans', 'totalFines'));
    }
}