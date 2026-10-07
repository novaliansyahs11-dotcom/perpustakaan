@extends('layouts.app')

@section('title', 'Edit Buku - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <h1 class="h3 fw-bold">Edit Informasi Buku</h1>
    <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

<div class="card border-0 shadow-sm" style="max-width: 850px;">
    <div class="card-body p-4">
        <form action="{{ route('books.update', $book->id) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kategori</label>
                    <select name="category_id" class="form-select" required>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ $book->category_id == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">ISBN</label>
                    <input type="text" name="isbn" class="form-control" value="{{ old('isbn', $book->isbn) }}" required>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Judul Buku</label>
                    <input type="text" name="title" class="form-control" value="{{ old('title', $book->title) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Penulis</label>
                    <input type="text" name="author" class="form-control" value="{{ old('author', $book->author) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Penerbit</label>
                    <input type="text" name="publisher" class="form-control" value="{{ old('publisher', $book->publisher) }}" required>
                </div>

                <div class="col-md-6">
                    <label class="form-label fw-semibold">Tahun Terbit</label>
                    <input type="number" name="publication_year" class="form-control" value="{{ old('publication_year', $book->publication_year) }}" required>
                </div>
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Nilai Buku / Acuan Denda (Rp)</label>
                    <input type="number" name="price" class="form-control" value="{{ old('price', $book->price) }}" required>
                </div>

                <div class="col-md-12">
                    <label class="form-label fw-semibold">Sampul Buku</label>
                    <div class="d-flex align-items-center gap-3 mb-2">
                        @if($book->cover_image)
                            <img src="{{ asset('storage/' . $book->cover_image) }}" alt="Cover" class="rounded border shadow-sm object-fit-cover" style="width: 70px; height: 95px;">
                        @endif
                        <div class="flex-grow-1">
                            <input type="file" name="cover_image" class="form-control" accept="image/*">
                            <small class="text-muted">Pilih gambar baru jika ingin mengganti sampul saat ini (Maksimal 2MB).</small>
                        </div>
                    </div>
                </div>

                <div class="col-12">
                    <label class="form-label fw-semibold">Deskripsi / Sinopsis</label>
                    <textarea name="description" rows="4" class="form-control">{{ old('description', $book->description) }}</textarea>
                </div>

                <div class="col-12 mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary px-4 py-2">
                        <i class="bi bi-save me-1"></i> Simpan Perubahan
                    </button>
                    <a href="{{ route('books.index') }}" class="btn btn-outline-secondary px-4 py-2">Batal</a>
                </div>
            </div>
        </form>
    </div>
</div>
@endsection