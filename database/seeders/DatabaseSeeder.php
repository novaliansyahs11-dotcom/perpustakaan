<?php

namespace Database\Seeders;

use App\Models\BookCopy;
use App\Models\Loan;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Buat Akun Admin & Member Netral (Otomatis Terverifikasi)
        $admin = User::updateOrCreate(
            ['email' => 'admin@perpus.test'],
            [
                'name' => 'Petugas Perpustakaan',
                'password' => Hash::make('password'),
                'role' => 'admin',
                'phone' => '081234567890',
                'id_card_type' => 'ktp',
                'id_card_number' => '3274010101900001',
                'email_verified_at' => now(),
                'is_active' => true,
            ]
        );

        $membersData = [
            ['name' => 'Ahmad Fauzi', 'email' => 'ahmad.fauzi@student.ac.id', 'phone' => '081211112222', 'type' => 'ktm', 'id_num' => '2024101001'],
            ['name' => 'Dimas Wicaksono', 'email' => 'dimas.w@student.ac.id', 'phone' => '081222223333', 'type' => 'ktm', 'id_num' => '2024101002'],
            ['name' => 'Anisa Rahmawati', 'email' => 'anisa.r@student.ac.id', 'phone' => '081233334444', 'type' => 'ktm', 'id_num' => '2024101003'],
            ['name' => 'Fajar Nugraha', 'email' => 'fajar.n@student.ac.id', 'phone' => '081244445555', 'type' => 'ktp', 'id_num' => '3171012304950005'],
            ['name' => 'Nadia Putri', 'email' => 'nadia.putri@student.ac.id', 'phone' => '081255556666', 'type' => 'ktm', 'id_num' => '2024101004'],
            ['name' => 'Bagus Setiawan', 'email' => 'bagus.s@student.ac.id', 'phone' => '081266667777', 'type' => 'sim', 'id_num' => '990112345600'],
        ];

        $users = [];
        foreach ($membersData as $m) {
            $users[] = User::updateOrCreate(
                ['email' => $m['email']],
                [
                    'name' => $m['name'],
                    'password' => Hash::make('password'),
                    'role' => 'member',
                    'phone' => $m['phone'],
                    'id_card_type' => $m['type'],
                    'id_card_number' => $m['id_num'],
                    'email_verified_at' => now(), // <-- Otomatis terverifikasi untuk setiap akun mahasiswa
                    'is_active' => true,
                ]
            );
        }

        // 2. Panggil Seeder 36 Buku Milik Kamu
        $this->call(BulkBookSeeder::class);

        // 3. Buat Data Skenario Sirkulasi & Denda (Agar Dashboard Langsung Terisi & Ada Contoh Kasus)
        $allCopies = BookCopy::with('book')->get();
        if ($allCopies->count() >= 10) {
            $now = Carbon::now();

            // Skenario A: Pinjaman Sukses Tepat Waktu (Bulan-bulan sebelumnya)
            $pastDates = [
                $now->copy()->subMonths(3),
                $now->copy()->subMonths(2),
                $now->copy()->subMonths(1),
                $now->copy()->subWeeks(3),
            ];

            foreach ($pastDates as $i => $pDate) {
                Loan::firstOrCreate(
                    ['book_copy_id' => $allCopies[$i]->id, 'borrow_date' => $pDate->format('Y-m-d')],
                    [
                        'user_id' => $users[$i % count($users)]->id,
                        'admin_id' => $admin->id,
                        'due_date' => $pDate->copy()->addDays(7)->format('Y-m-d'),
                        'return_date' => $pDate->copy()->addDays(5)->format('Y-m-d'),
                        'late_days' => 0,
                        'fine_amount' => 0,
                        'status' => 'returned',
                    ]
                );
            }

            // Skenario B: Telat 4 Hari (Minggu ke-1: Denda 10% dari Harga Buku)
            $copyDenda1 = $allCopies[4];
            $fine1 = ($copyDenda1->book->price ?? 100000) * 0.10;
            Loan::firstOrCreate(
                ['book_copy_id' => $copyDenda1->id, 'status' => 'returned', 'late_days' => 4],
                [
                    'user_id' => $users[0]->id,
                    'admin_id' => $admin->id,
                    'borrow_date' => $now->copy()->subDays(18)->format('Y-m-d'),
                    'due_date' => $now->copy()->subDays(11)->format('Y-m-d'),
                    'return_date' => $now->copy()->subDays(7)->format('Y-m-d'),
                    'late_days' => 4,
                    'fine_amount' => $fine1,
                    'status' => 'returned',
                ]
            );

            // Skenario C: Telat 10 Hari (Minggu ke-2: Denda 20% dari Harga Buku)
            $copyDenda2 = $allCopies[6];
            $fine2 = ($copyDenda2->book->price ?? 100000) * 0.20;
            Loan::firstOrCreate(
                ['book_copy_id' => $copyDenda2->id, 'status' => 'returned', 'late_days' => 10],
                [
                    'user_id' => $users[1]->id,
                    'admin_id' => $admin->id,
                    'borrow_date' => $now->copy()->subDays(24)->format('Y-m-d'),
                    'due_date' => $now->copy()->subDays(17)->format('Y-m-d'),
                    'return_date' => $now->copy()->subDays(7)->format('Y-m-d'),
                    'late_days' => 10,
                    'fine_amount' => $fine2,
                    'status' => 'returned',
                ]
            );

            // Skenario D: Sedang Dipinjam & Telat Aktif 3 Hari (Denda Berjalan 10%)
            $copyAktifTelat = $allCopies[7];
            $copyAktifTelat->update(['status' => 'borrowed']);
            $fineAktif = ($copyAktifTelat->book->price ?? 100000) * 0.10;
            Loan::firstOrCreate(
                ['book_copy_id' => $copyAktifTelat->id, 'status' => 'borrowed'],
                [
                    'user_id' => $users[2]->id,
                    'admin_id' => $admin->id,
                    'borrow_date' => $now->copy()->subDays(10)->format('Y-m-d'),
                    'due_date' => $now->copy()->subDays(3)->format('Y-m-d'),
                    'return_date' => null,
                    'late_days' => 3,
                    'fine_amount' => $fineAktif,
                    'status' => 'borrowed',
                ]
            );

            // Skenario E: Sedang Dipinjam Normal (Belum Jatuh Tempo)
            $copyNormal = $allCopies[8];
            $copyNormal->update(['status' => 'borrowed']);
            Loan::firstOrCreate(
                ['book_copy_id' => $copyNormal->id, 'status' => 'borrowed'],
                [
                    'user_id' => $users[3]->id,
                    'admin_id' => $admin->id,
                    'borrow_date' => $now->copy()->subDays(2)->format('Y-m-d'),
                    'due_date' => $now->copy()->addDays(5)->format('Y-m-d'),
                    'return_date' => null,
                    'late_days' => 0,
                    'fine_amount' => 0,
                    'status' => 'borrowed',
                ]
            );
        }
    }
}