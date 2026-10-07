<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        $user = Auth::user();

        if ($user->role === 'admin') {
            // 1. Total buku (judul)
            $totalBooks = Book::count();

            // 2. Total eksemplar fisik
            $totalCopies = BookCopy::count();

            // 3. Buku tersedia
            $availableCopies = BookCopy::where('status', 'available')->count();

            // 4. Buku sedang dipinjam
            $borrowedCopies = BookCopy::where('status', 'borrowed')->count();

            // 5. Member aktif
            $activeMembers = User::where('role', 'member')->where('is_active', true)->count();

            // 6. Total transaksi
            $totalTransactions = Loan::count();

            // 7. Total denda (Denda selesai + Denda estimasi berjalan dari pinjaman terlambat)
            $settledFines = Loan::where('status', 'returned')->sum('fine_amount');
            
            $activeLoans = Loan::with('bookCopy.book')->where('status', 'borrowed')->get();
            $pendingFines = 0;
            $today = Carbon::now()->startOfDay();

            foreach ($activeLoans as $loan) {
                $dueDate = Carbon::parse($loan->due_date)->startOfDay();
                if ($today->gt($dueDate)) {
                    $daysLate = $today->diffInDays($dueDate);
                    $price = $loan->bookCopy->book->price ?? 0;
                    if ($daysLate <= 7) {
                        $pendingFines += ($price * 0.10);
                    } elseif ($daysLate <= 14) {
                        $pendingFines += ($price * 0.20);
                    } else {
                        $pendingFines += ($price * 0.30);
                    }
                }
            }
            $totalFines = $settledFines + $pendingFines;

            // 8. Buku paling banyak dipinjam (Top 5 Leaderboard)
            $mostBorrowedBooks = Book::select('books.id', 'books.title', 'books.author', DB::raw('COUNT(loans.id) as total_loans'))
                ->join('book_copies', 'books.id', '=', 'book_copies.book_id')
                ->join('loans', 'book_copies.id', '=', 'loans.book_copy_id')
                ->groupBy('books.id', 'books.title', 'books.author')
                ->orderByDesc('total_loans')
                ->take(5)
                ->get();

            // 9. Statistik peminjaman (Tren peminjaman 6 bulan terakhir)
            $monthlyLabels = [];
            $monthlyData = [];
            for ($i = 5; $i >= 0; $i--) {
                $date = Carbon::now()->subMonths($i);
                $monthlyLabels[] = $date->translatedFormat('M Y');
                $monthlyData[] = Loan::whereYear('borrow_date', $date->year)
                    ->whereMonth('borrow_date', $date->month)
                    ->count();
            }

            // Distribusi Kategori Buku
            $categories = Category::withCount('books')->get();
            $categoryLabels = $categories->pluck('name');
            $categoryCounts = $categories->pluck('books_count');

            // Aktivitas Peminjaman Terkini
            $recentLoans = Loan::with(['user', 'bookCopy.book'])->latest()->take(5)->get();

            return view('dashboard.admin', compact(
                'totalBooks',
                'totalCopies',
                'availableCopies',
                'borrowedCopies',
                'activeMembers',
                'totalTransactions',
                'totalFines',
                'settledFines',
                'pendingFines',
                'mostBorrowedBooks',
                'monthlyLabels',
                'monthlyData',
                'categoryLabels',
                'categoryCounts',
                'recentLoans'
            ));
        }

        // Tampilan untuk Role Member
        $myActiveLoans = Loan::with('bookCopy.book')
            ->where('user_id', $user->id)
            ->where('status', 'borrowed')
            ->get();

        $myLoanHistory = Loan::with('bookCopy.book')
            ->where('user_id', $user->id)
            ->where('status', 'returned')
            ->latest()
            ->take(5)
            ->get();

        return view('dashboard.member', compact('myActiveLoans', 'myLoanHistory'));
    }

    public function browseBooks(Request $request)
    {
        $categories = Category::all();
        $query = Book::with(['category', 'copies']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $books = $query->paginate(9)->withQueryString();
        return view('member.browse', compact('books', 'categories'));
    }

    public function myLoans()
    {
        $loans = Loan::with('bookCopy.book')
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(10);

        return view('member.loans', compact('loans'));
    }

    // Cetak Lembar Rekapitulasi Riwayat Peminjaman Mandiri oleh Member
    public function printMyLoans()
    {
        $member = Auth::user();
        $loans = $member->loans()
            ->with(['bookCopy.book', 'admin'])
            ->latest()
            ->get();

        $totalFines = $member->loans()->sum('fine_amount');
        $activeLoansCount = $member->loans()->where('status', 'borrowed')->count();

        return view('members.print_report', compact('member', 'loans', 'totalFines', 'activeLoansCount'));
    }
}