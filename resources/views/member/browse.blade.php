@extends('layouts.app')

@section('title', 'Katalog Buku - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold mb-0">Katalog Koleksi Buku</h1>
        <small class="text-muted">Jelajahi koleksi buku dan cek ketersediaan fisik di rak perpustakaan.</small>
    </div>
</div>

{{-- Alert Notifikasi --}}
@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-check-circle-fill me-2"></i> {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <i class="bi bi-clock-history me-2"></i> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<form action="{{ Route::has('catalog.public') && Auth::guest() ? route('catalog.public') : route('member.browse') }}" method="GET" class="card border-0 shadow-sm p-3 mb-4">
    <div class="row g-2">
        <div class="col-md-7">
            <input type="text" name="search" class="form-control" placeholder="Cari judul buku, penulis, atau nomor ISBN..." value="{{ request('search') }}">
        </div>
        <div class="col-md-3">
            <select name="category_id" class="form-select">
                <option value="">-- Semua Kategori --</option>
                @foreach($categories as $cat)
                    <option value="{{ $cat->id }}" {{ request('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                @endforeach
            </select>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search me-1"></i> Cari
            </button>
        </div>
    </div>
</form>

<div class="row g-4">
    @forelse($books as $book)
    @php
        // Ambil unit eksemplar fisik yang siap dipinjam di rak
        $availableCopies = $book->copies->where('status', 'available');
        $availableCount = $availableCopies->count();

        // Tentukan URL detail buku berdasarkan status login/route yang tersedia
        $detailRoute = Route::has('catalog.show') && Auth::guest() 
            ? route('catalog.show', $book->id) 
            : (Route::has('member.books.show') ? route('member.books.show', $book->id) : route('catalog.show', $book->id));
    @endphp
    <div class="col-md-4">
        <div class="card h-100 border-0 shadow-sm overflow-hidden d-flex flex-column">
            <div style="height: 200px; background-color: #212529;" class="d-flex align-items-center justify-content-center overflow-hidden position-relative">
                @if($book->cover_image)
                    @php
                        $coverUrl = \Illuminate\Support\Str::startsWith($book->cover_image, ['http://', 'https://'])
                            ? $book->cover_image
                            : asset('storage/' . $book->cover_image);
                    @endphp
                    <img src="{{ $coverUrl }}" alt="{{ $book->title }}" class="w-100 h-100 object-fit-cover" onerror="this.onerror=null; this.src='https://placehold.co/400x600?text=No+Cover';">
                @else
                    <div class="text-center text-secondary">
                        <i class="bi bi-book fs-1 mb-1 d-block"></i>
                        <small>Tanpa Sampul</small>
                    </div>
                @endif
                <span class="position-absolute top-0 end-0 m-2 badge {{ $availableCount > 0 ? 'bg-success' : 'bg-danger' }}">
                    {{ $availableCount > 0 ? $availableCount . ' Eksemplar Tersedia' : 'Stok Habis' }}
                </span>
            </div>
            <div class="card-body d-flex flex-column p-3">
                <span class="badge bg-secondary mb-2 align-self-start">{{ $book->category->name ?? 'Umum' }}</span>
                <h5 class="fw-bold mb-1 fs-6 text-truncate" title="{{ $book->title }}">{{ $book->title }}</h5>
                <p class="text-muted small mb-2">Penulis: {{ $book->author }} ({{ $book->publication_year }})</p>
                <p class="small text-secondary flex-grow-1">{{ Str::limit($book->description ?? 'Tidak ada sinopsis ringkas.', 90) }}</p>
                
                <hr class="my-2">
                
                <div class="d-grid">
                    {{-- Tombol Lihat Detail Buku (Selalu Tampil) --}}
                    <a href="{{ $detailRoute }}" class="btn btn-sm btn-outline-primary w-100 fw-semibold">
                        <i class="bi bi-eye me-1"></i> Lihat Detail Buku
                    </a>
                </div>
            </div>
        </div>
    </div>
    @empty
    <div class="col-12 text-center py-5 text-muted">
        <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
        Buku yang dicari tidak ditemukan.
    </div>
    @endforelse
</div>

<div class="mt-4">
    {{ $books->links('pagination::bootstrap-5') }}
</div>
@endsection