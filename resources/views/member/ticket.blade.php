@extends('layouts.app')

@section('title', 'Tiket Barcode Peminjaman Mandiri')

@section('content')
<div class="container py-4">
    <div class="row justify-content-center">
        <div class="col-md-7 col-lg-6">
            
            {{-- Tombol Navigasi Kembali --}}
            <div class="mb-3 d-flex justify-content-between align-items-center">
                <a href="{{ route('member.browse') }}" class="btn btn-outline-secondary btn-sm">
                    <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
                </a>
                <span class="badge bg-light text-dark border">
                    <i class="bi bi-shield-check text-primary me-1"></i> Fast-Pass Token
                </span>
            </div>

            {{-- Flash Alert Notifikasi --}}
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show mb-3 py-2 small" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            @if(session('warning'))
                <div class="alert alert-warning alert-dismissible fade show mb-3 py-2 small" role="alert">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ session('warning') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            {{-- Kartu Utama Tiket Digital --}}
            <div class="card border-0 shadow-sm rounded-4 overflow-hidden">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h5 class="fw-bold mb-0">TIKET PINJAM MANDIRI</h5>
                    <small class="text-white-50">Tunjukkan QR Code ini langsung ke Petugas Sirkulasi</small>
                </div>

                <div class="card-body p-4 text-center">
                    
                    @if($token->status === 'pending' && \Carbon\Carbon::now()->lte($token->expires_at))
                        
                        {{-- 1. Status Aktif: QR Code & Countdown Timer --}}
                        <div class="my-2">
                            <img src="https://api.qrserver.com/v1/create-qr-code/?size=200x200&data={{ $token->token_code }}" 
                                 alt="QR Code Tiket" 
                                 class="img-thumbnail p-2 shadow-sm rounded-3">
                        </div>

                        {{-- Tampilan String Kode Token --}}
                        <div class="badge bg-dark fs-5 py-2 px-3 font-monospace my-2 letter-spacing-1">
                            {{ $token->token_code }}
                        </div>

                        {{-- Hitung Mundur Live (Countdown) --}}
                        <div class="alert alert-warning py-2 px-3 my-3 d-flex align-items-center justify-content-center gap-2">
                            <i class="bi bi-clock-history fs-5 text-danger"></i>
                            <div class="text-start">
                                <div class="small text-muted" style="font-size: 0.75rem;">MASA BERLAKU TIKET:</div>
                                <span id="countdown" class="fw-bold text-danger fs-6">Menghitung...</span>
                            </div>
                        </div>

                        {{-- Detail Informasi Buku & Peminjam --}}
                        <div class="bg-light p-3 rounded-3 text-start small mt-3 border">
                            <div class="row g-2">
                                <div class="col-4 text-muted">Peminjam</div>
                                <div class="col-8 fw-semibold text-dark">: {{ $token->user->name }}</div>

                                <div class="col-4 text-muted">Judul Buku</div>
                                <div class="col-8 fw-semibold text-dark">: {{ $token->bookCopy->book->title }}</div>

                                <div class="col-4 text-muted">No. Eksemplar</div>
                                <div class="col-8 fw-semibold text-primary">: {{ $token->bookCopy->copy_code }}</div>

                                <div class="col-4 text-muted">Batas Ambil</div>
                                <div class="col-8 text-muted">: {{ \Carbon\Carbon::parse($token->expires_at)->format('H:i') }} WIB (Hari Ini)</div>
                            </div>
                        </div>

                        <p class="text-muted small mt-3 mb-0" style="font-size: 0.8rem;">
                            <i class="bi bi-info-circle me-1"></i>
                            Setelah lewat 60 menit, tiket otomatis batal dan buku kembali tersedia di rak untuk orang lain.
                        </p>

                    @elseif($token->status === 'claimed')

                        {{-- 2. Status Berhasil Dipinjam --}}
                        <div class="py-4">
                            <div class="text-success mb-3">
                                <i class="bi bi-check-circle-fill" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="fw-bold text-success">Peminjaman Berhasil Diproses!</h5>
                            <p class="text-muted small mb-3">
                                Tiket telah dipindai oleh petugas meja sirkulasi. Buku fisik telah diserahkan dan masa pinjam resmi 7 hari telah berjalan.
                            </p>
                            <a href="{{ route('member.loans') }}" class="btn btn-outline-primary btn-sm">
                                <i class="bi bi-journal-bookmark me-1"></i> Lihat di Pinjaman Saya
                            </a>
                        </div>

                    @else

                        {{-- 3. Status Expired (Kedaluwarsa) --}}
                        <div class="py-4">
                            <div class="text-danger mb-3">
                                <i class="bi bi-x-circle-fill" style="font-size: 4rem;"></i>
                            </div>
                            <h5 class="fw-bold text-danger">Tiket Kedaluwarsa (Expired)</h5>
                            <p class="text-muted small mb-3">
                                Batas waktu pengambilan 60 menit telah habis. Status eksemplar fisik buku telah dikembalikan secara otomatis ke rak perpustakaan.
                            </p>
                            <a href="{{ route('member.browse') }}" class="btn btn-primary btn-sm">
                                <i class="bi bi-search me-1"></i> Cari Buku Kembali
                            </a>
                        </div>

                    @endif

                </div>
            </div>

        </div>
    </div>
</div>

{{-- Script Countdown Timer 60 Menit --}}
@if($token->status === 'pending' && \Carbon\Carbon::now()->lte($token->expires_at))
<script>
    const expireTime = new Date("{{ \Carbon\Carbon::parse($token->expires_at)->toIso8601String() }}").getTime();

    const timer = setInterval(function() {
        const now = new Date().getTime();
        const distance = expireTime - now;

        if (distance <= 0) {
            clearInterval(timer);
            document.getElementById("countdown").innerHTML = "KEDALUWARSA";
            window.location.reload(); // Refresh otomatis agar status berubah jadi Expired
            return;
        }

        const minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
        const seconds = Math.floor((distance % (1000 * 60)) / 1000);

        document.getElementById("countdown").innerHTML = 
            (minutes < 10 ? "0" + minutes : minutes) + " Menit " + 
            (seconds < 10 ? "0" + seconds : seconds) + " Detik";
    }, 1000);
</script>
@endif
@endsection