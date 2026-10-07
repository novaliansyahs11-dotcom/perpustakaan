@extends('layouts.app')

@section('title', 'Detail Buku - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <h1 class="h3 fw-bold">{{ $book->title }}</h1>
    <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">Kembali</a>
</div>

<div class="row g-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm p-3">
            @if($book->cover_image)
                <div class="text-center mb-3">
                    <img src="{{ asset('storage/' . $book->cover_image) }}" alt="{{ $book->title }}" class="img-fluid rounded shadow" style="max-height: 280px; object-fit: cover;">
                </div>
            @endif
            <h5 class="fw-bold mb-3">Informasi Buku</h5>
            <p class="mb-1"><strong>ISBN:</strong> {{ $book->isbn }}</p>
            <p class="mb-1"><strong>Kategori:</strong> {{ $book->category->name }}</p>
            <p class="mb-1"><strong>Penulis:</strong> {{ $book->author }}</p>
            <p class="mb-1"><strong>Penerbit:</strong> {{ $book->publisher }} ({{ $book->publication_year }})</p>
            <p class="mb-1"><strong>Nilai Acuan Denda:</strong> Rp {{ number_format($book->price, 0, ',', '.') }}</p>
            <hr>
            <p class="text-muted small">{{ $book->description ?? 'Tidak ada sinopsis.' }}</p>
        </div>
    </div>
    <div class="col-md-7">
        <div class="card border-0 shadow-sm p-3">
            <h5 class="fw-bold mb-3">Daftar Fisik Eksemplar (Barcode)</h5>
            <ul class="list-group list-group-flush">
                @foreach($book->copies as $copy)
                <li class="list-group-item d-flex justify-content-between align-items-center px-0">
                    <div>
                        <span class="badge bg-dark me-2">{{ $copy->copy_code }}</span>
                        <small class="text-muted">ID Fisik #{{ $copy->id }}</small>
                    </div>
                    @if($copy->status === 'available')
                        <span class="badge bg-success">Tersedia</span>
                    @elseif($copy->status === 'borrowed')
                        <span class="badge bg-warning text-dark">Sedang Dipinjam</span>
                    @else
                        <span class="badge bg-danger">{{ ucfirst($copy->status) }}</span>
                    @endif
                </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>
@endsection