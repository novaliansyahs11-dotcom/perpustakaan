@extends('layouts.app')

@section('title', 'Data Buku - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold mb-0">Data Katalog Buku</h1>
        <small class="text-muted">Kelola master buku, stok eksemplar fisik barcode, dan cover.</small>
    </div>
    <a href="{{ route('books.create') }}" class="btn btn-primary shadow-sm">
        <i class="bi bi-plus-circle me-1"></i> Tambah Buku Baru
    </a>
</div>

<!-- Form Search & Filter -->
<div class="card border-0 shadow-sm p-3 mb-4">
    <form action="{{ route('books.index') }}" method="GET">
        <div class="row g-2 align-items-center">
            <div class="col-md-6">
                <div class="input-group">
                    <span class="input-group-text bg-white border-end-0 text-muted">
                        <i class="bi bi-search"></i>
                    </span>
                    <input type="text" name="search" class="form-control border-start-0" 
                           placeholder="Cari berdasarkan judul buku, penulis, atau ISBN..." 
                           value="{{ request('search') }}">
                </div>
            </div>
            <div class="col-md-4">
                <select name="category_id" class="form-select">
                    <option value="">-- Semua Kategori --</option>
                    @foreach($categories as $category)
                        <option value="{{ $category->id }}" {{ request('category_id') == $category->id ? 'selected' : '' }}>
                            {{ $category->name }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="col-md-2 d-flex gap-2">
                <button type="submit" class="btn btn-primary flex-grow-1">
                    Cari
                </button>
                @if(request('search') || request('category_id'))
                    <a href="{{ route('books.index') }}" class="btn btn-outline-secondary" title="Reset Pencarian">
                        <i class="bi bi-x-circle"></i>
                    </a>
                @endif
            </div>
        </div>
    </form>
</div>

<!-- Tabel Koleksi Buku -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 50px;">No</th>
                        <th style="width: 70px;">Sampul</th>
                        <th>ISBN</th>
                        <th>Judul Buku</th>
                        <th>Kategori</th>
                        <th>Penulis / Penerbit</th>
                        <th>Harga</th>
                        <th>Eksemplar</th>
                        <th class="text-center" style="width: 170px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($books as $index => $book)
                    <tr>
                        <td class="ps-3">{{ $books->firstItem() + $index }}</td>
                        <td>
                            @if($book->cover_image)
                                @php
                                    $coverUrl = \Illuminate\Support\Str::startsWith($book->cover_image, ['http://', 'https://'])
                                        ? $book->cover_image
                                        : asset('storage/' . $book->cover_image);
                                @endphp
                                <img src="{{ $coverUrl }}" 
                                     alt="{{ $book->title }}" 
                                     class="rounded shadow-sm object-fit-cover border" 
                                     style="width: 48px; height: 68px;"
                                     onerror="this.onerror=null; this.src='https://placehold.co/400x600?text=No+Cover';">
                            @else
                                <div class="bg-light rounded d-flex align-items-center justify-content-center text-muted border" style="width: 48px; height: 68px;">
                                    <i class="bi bi-book fs-4"></i>
                                </div>
                            @endif
                        </td>
                        <td><small class="text-muted">{{ $book->isbn }}</small></td>
                        <td class="fw-semibold">{{ $book->title }}</td>
                        <td><span class="badge bg-secondary">{{ $book->category->name }}</span></td>
                        <td>
                            {{ $book->author }}<br>
                            <small class="text-muted">{{ $book->publisher }} ({{ $book->publication_year }})</small>
                        </td>
                        <td>Rp {{ number_format($book->price, 0, ',', '.') }}</td>
                        <td>
                            <span class="badge bg-info text-dark">
                                {{ $book->copies->where('status', 'available')->count() }} / {{ $book->copies->count() }} Tersedia
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="btn-group" role="group">
                                <!-- Tombol Detail & Barcode Eksemplar -->
                                <a href="{{ route('books.show', $book->id) }}" class="btn btn-sm btn-outline-info" title="Lihat Detail & Eksemplar">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <!-- Tombol Edit Buku -->
                                <a href="{{ route('books.edit', $book->id) }}" class="btn btn-sm btn-outline-warning" title="Edit Data Buku">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- Tombol Tambah Stok Eksemplar Fisik Cepat -->
                                <button type="button" class="btn btn-sm btn-outline-success" data-bs-toggle="modal" data-bs-target="#addCopyModal{{ $book->id }}" title="Tambah Eksemplar Fisik">
                                    <i class="bi bi-plus-square"></i>
                                </button>

                                <!-- Tombol Hapus Buku -->
                                <form action="{{ route('books.destroy', $book->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus buku ini beserta seluruh data eksemplarnya?');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Buku">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>

                            <!-- Modal Tambah Eksemplar Cepat -->
                            <div class="modal fade text-start" id="addCopyModal{{ $book->id }}" tabindex="-1" aria-hidden="true">
                                <div class="modal-dialog modal-sm">
                                    <div class="modal-content">
                                        <form action="{{ route('books.add-copy', $book->id) }}" method="POST">
                                            @csrf
                                            <div class="modal-header">
                                                <h6 class="modal-title fw-bold">Tambah Eksemplar</h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <p class="small text-muted mb-2">Buku: <strong>{{ Str::limit($book->title, 30) }}</strong></p>
                                                <label class="form-label small fw-semibold">Jumlah Fisik Tambahan</label>
                                                <input type="number" name="additional_copies" class="form-control" value="1" min="1" max="10" required>
                                            </div>
                                            <div class="modal-footer p-2">
                                                <button type="submit" class="btn btn-sm btn-primary w-100">Tambah Fisik</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-5 text-muted">
                            <i class="bi bi-search fs-2 d-block mb-2 text-secondary"></i>
                            Buku yang dicari tidak ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-3">
        {{ $books->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection