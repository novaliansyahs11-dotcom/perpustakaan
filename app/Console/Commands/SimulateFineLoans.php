<?php

namespace App\Console\Commands;

use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Console\Command;

class SimulateFineLoans extends Command
{
    protected $signature = 'simulate:fines';
    protected $description = 'Simulasi data peminjaman yang lewat jatuh tempo untuk pengujian denda';

    public function handle()
    {
        $admin = User::where('role', 'admin')->first();
        $member = User::where('role', 'member')->first();

        if (!$admin || !$member) {
            $this->error('Admin atau Member belum ada di database.');
            return;
        }

        // Ambil 2 eksemplar buku yang statusnya 'available'
        $copies = BookCopy::with('book')->where('status', 'available')->take(2)->get();

        if ($copies->count() < 2) {
            $this->error('Stok eksemplar yang tersedia kurang dari 2.');
            return;
        }

        // Simulasi 1: Telat 3 hari (Pinjam 10 hari lalu, jatuh tempo 3 hari lalu) -> Denda 10%
        $copy1 = $copies[0];
        $borrowDate1 = Carbon::now()->subDays(10)->toDateString();
        $dueDate1 = Carbon::now()->subDays(3)->toDateString();

        Loan::create([
            'user_id' => $member->id,
            'book_copy_id' => $copy1->id,
            'admin_id' => $admin->id,
            'borrow_date' => $borrowDate1,
            'due_date' => $dueDate1,
            'status' => 'borrowed',
        ]);
        $copy1->update(['status' => 'borrowed']);

        // Simulasi 2: Telat 10 hari (Pinjam 17 hari lalu, jatuh tempo 10 hari lalu) -> Denda 20%
        $copy2 = $copies[1];
        $borrowDate2 = Carbon::now()->subDays(17)->toDateString();
        $dueDate2 = Carbon::now()->subDays(10)->toDateString();

        Loan::create([
            'user_id' => $member->id,
            'book_copy_id' => $copy2->id,
            'admin_id' => $admin->id,
            'borrow_date' => $borrowDate2,
            'due_date' => $dueDate2,
            'status' => 'borrowed',
        ]);
        $copy2->update(['status' => 'borrowed']);

        $this->info("Simulasi berhasil dibuat!");
        $this->info("1. Buku '{$copy1->book->title}' (Harga: Rp " . number_format($copy1->book->price, 0, ',', '.') . ") telat 3 hari -> Ekspektasi denda 10%: Rp " . number_format($copy1->book->price * 0.10, 0, ',', '.'));
        $this->info("2. Buku '{$copy2->book->title}' (Harga: Rp " . number_format($copy2->book->price, 0, ',', '.') . ") telat 10 hari -> Ekspektasi denda 20%: Rp " . number_format($copy2->book->price * 0.20, 0, ',', '.'));
    }
}