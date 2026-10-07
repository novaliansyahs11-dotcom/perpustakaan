@extends('layouts.app')

@section('title', $book->title . ' - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold mb-0">{{ $book->title }}</h1>
        <small class="text-muted">Detail katalog koleksi dan pantau ketersediaan eksemplar fisik di rak.</small>
    </div>
    <a href="{{ route('member.browse') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Katalog
    </a>
</div>

<div class="row g-4">
    <!-- Kolom Cover & Prosedur Peminjaman -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 text-center">
            @if($book->cover_image)
                @php
                    $coverUrl = \Illuminate\Support\Str::startsWith($book->cover_image, ['http://', 'https://'])
                        ? $book->cover_image
                        : asset('storage/' . $book->cover_image);
                @endphp
                <img src="{{ $coverUrl }}" 
                     alt="{{ $book->title }}" 
                     class="img-fluid rounded shadow-sm mb-3 mx-auto border" 
                     style="max-height: 380px; width: 100%; object-fit: cover;"
                     onerror="this.onerror=null; this.src='https://placehold.co/400x600?text=No+Cover';">
            @else
                <div class="bg-light rounded p-5 mb-3 text-muted border">
                    <i class="bi bi-journal fs-1"></i>
                    <p class="mb-0 mt-2 fw-semibold">Tanpa Sampul</p>
                </div>
            @endif

            <div class="p-3 bg-light rounded text-start border">
                <h6 class="fw-bold mb-1 text-primary"><i class="bi bi-info-circle me-1"></i> Prosedur Peminjaman</h6>
                <small class="text-secondary d-block">
                    Sistem ini tidak melayani booking online. Silakan ambil buku fisik di rak perpustakaan dan bawa ke <strong>Meja Petugas</strong> untuk dicatat peminjamannya.
                </small>
            </div>
        </div>
    </div>

    <!-- Kolom Rincian Informasi Buku & Eksemplar -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm p-4">
            <h4 class="fw-bold mb-3 border-bottom pb-2">Informasi Rinci</h4>
            
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Kategori</div>
                <div class="col-sm-8 fw-semibold">
                    <span class="badge bg-primary">{{ $book->category->name }}</span>
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Penulis</div>
                <div class="col-sm-8 fw-semibold text-dark">{{ $book->author }}</div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Penerbit & Tahun</div>
                <div class="col-sm-8">{{ $book->publisher }} ({{ $book->publication_year }})</div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Nomor ISBN</div>
                <div class="col-sm-8"><code class="fs-6">{{ $book->isbn }}</code></div>
            </div>

            <!-- Field Sesuai Scope Plan Poin E: Harga Buku (Acuan Denda) -->
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Harga Buku (Nilai Acuan)</div>
                <div class="col-sm-8 fw-bold text-success fs-5">
                    Rp {{ number_format($book->price, 0, ',', '.') }}
                </div>
            </div>

            <!-- Field Sesuai Scope Plan Poin E: Ketersediaan & Breakdown Eksemplar -->
            @php
                $totalCopies = $book->copies->count();
                $borrowedCount = $book->copies->where('status', 'borrowed')->count();
            @endphp
            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Status Ketersediaan</div>
                <div class="col-sm-8">
                    @if($availableCopiesCount > 0)
                        <span class="badge bg-success fs-6 px-3 py-2">
                            <i class="bi bi-check-circle me-1"></i> Tersedia untuk Dipinjam
                        </span>
                    @else
                        <span class="badge bg-danger fs-6 px-3 py-2">
                            <i class="bi bi-x-circle me-1"></i> Sedang Tidak Tersedia (Habis)
                        </span>
                    @endif
                </div>
            </div>

            <div class="row mb-3">
                <div class="col-sm-4 text-muted">Rincian Eksemplar Fisik</div>
                <div class="col-sm-8">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-light text-dark border">
                            Total: <strong>{{ $totalCopies }}</strong> Eksemplar
                        </span>
                        <span class="badge bg-success-subtle text-success border border-success-subtle">
                            Tersedia: <strong>{{ $availableCopiesCount }}</strong>
                        </span>
                        <span class="badge bg-warning-subtle text-warning-emphasis border border-warning-subtle">
                            Dipinjam: <strong>{{ $borrowedCount }}</strong>
                        </span>
                    </div>
                    <small class="text-muted d-block mt-2" style="font-size: 0.8rem;">
                        Setiap buku fisik memiliki barcode/ID unik tersendiri untuk pencatatan di meja sirkulasi.
                    </small>
                </div>
            </div>

            <hr class="my-3">

            <h5 class="fw-bold mb-2">Sinopsis & Deskripsi</h5>
            <p class="text-secondary leading-relaxed mb-0" style="line-height: 1.7; text-align: justify;">
                {{ $book->description ?? 'Belum ada deskripsi atau sinopsis ringkas untuk buku ini.' }}
            </p>
        </div>
    </div>
</div>
@endsection