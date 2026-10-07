<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete(); // Member peminjam
            $table->foreignId('book_copy_id')->constrained()->cascadeOnDelete(); // Eksemplar buku
            $table->foreignId('admin_id')->constrained('users'); // Petugas/Admin yang memproses
            $table->date('borrow_date'); // Tanggal pinjam
            $table->date('due_date'); // Batas pengembalian (7 hari)
            $table->date('return_date')->nullable(); // Tanggal aktual kembali
            $table->integer('late_days')->default(0); // Jumlah hari keterlambatan
            $table->decimal('fine_amount', 12, 2)->default(0); // Denda berjalan / final
            $table->enum('status', ['borrowed', 'returned', 'lost'])->default('borrowed');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loans');
    }
};