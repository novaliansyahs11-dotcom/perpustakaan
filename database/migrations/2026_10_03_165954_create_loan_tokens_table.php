<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // 1. Tambahkan status 'reserved_temp' pada tabel book_copies
        // Mengubah enum status agar mendukung reservasi sementara 1 jam
        DB::statement("ALTER TABLE book_copies MODIFY COLUMN status ENUM('available', 'borrowed', 'reserved_temp') NOT NULL DEFAULT 'available'");

        // 2. Buat tabel loan_tokens untuk menyimpan tiket barcode 60 menit
        Schema::create('loan_tokens', function (Blueprint $table) {
            $table->id();
            $table->string('token_code', 64)->unique(); // Kode tiket/barcode QR (misal: TKN-ABCD1234)
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade'); // Peminjam
            $table->foreignId('book_copy_id')->constrained('book_copies')->onDelete('cascade'); // Fisik buku
            $table->dateTime('expires_at'); // Batas waktu 1 jam
            $table->enum('status', ['pending', 'claimed', 'expired'])->default('pending');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_tokens');
        DB::statement("ALTER TABLE book_copies MODIFY COLUMN status ENUM('available', 'borrowed') NOT NULL DEFAULT 'available'");
    }
};