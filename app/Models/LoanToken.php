<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LoanToken extends Model
{
    use HasFactory;

    protected $fillable = [
        'token_code',
        'user_id',
        'book_copy_id',
        'expires_at',
        'status',
    ];

    protected $casts = [
        'expires_at' => 'datetime',
    ];

    // Relasi ke Member (Mahasiswa)
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Relasi ke Buku Fisik (Eksemplar)
    public function bookCopy()
    {
        return $this->belongsTo(BookCopy::class);
    }
}