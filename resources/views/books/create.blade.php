@extends('layouts.app')

@section('title', 'Tambah Buku - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <h1 class="h3 fw-bold">Tambah Buku Baru</h1>
    <a href="{{ route('books.index') }}" class="btn btn-outline-secondary">Kembali</a>
</div>

{{-- Alert Error Global dari Validasi Controller --}}
@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert" style="max-width: 800px;">
        <div class="d-flex align-items-center">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5"></i>
            <div>
                <strong>Perhatian!</strong> Ada beberapa data input yang belum sesuai ketentuan:
                <ul class="mb-0 mt-1 ps-3">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="card border-0 shadow-sm" style="max-width: 800px;">
    <div class="card-body p-4">
        <form action="{{ route('books.store') }}" method="POST" enctype="multipart/form-data">
            @csrf
            <div class="row g-3">
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Kategori</label>
                    <select name="category_id" class="form-select @error('category_id') is-invalid @enderror" required>
                        <option value="">-- Pilih Kategori --</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('category_id') == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                    @error('category_id')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-semibold">ISBN</label>
                    <input type="text" name="isbn" class="form-control @error('isbn') is-invalid @enderror" 
                           placeholder="Contoh: 978-602-01-1234-5" value="{{ old('isbn') }}" required>
                    @error('isbn')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12">
                    <label class="form-label fw-semibold">Judul Buku</label>
                    <input type="text" name="title" class="form-control @error('title') is-invalid @enderror" 
                           value="{{ old('title') }}" required>
                    @error('title')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Penulis</label>
                    <input type="text" name="author" class="form-control @error('author') is-invalid @enderror" 
                           value="{{ old('author') }}" required>
                    @error('author')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-md-6">
                    <label class="form-label fw-semibold">Penerbit</label>
                    <input type="text" name="publisher" class="form-control @error('publisher') is-invalid @enderror" 
                           value="{{ old('publisher') }}" required>
                    @error('publisher')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                {{-- Input Tahun Terbit: Min 1900, cegah tombol minus --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Tahun Terbit</label>
                    <input type="number" 
                           name="publication_year" 
                           id="publication_year"
                           class="form-control @error('publication_year') is-invalid @enderror" 
                           value="{{ old('publication_year', 2026) }}" 
                           min="1900" 
                           max="2030"
                           onkeydown="if(event.key==='-'||event.key==='e'||event.key==='E'||event.key==='+') event.preventDefault();"
                           oninput="checkNonNegative(this, 'warn-year', 'Tahun tidak boleh minus atau di bawah 1900')"
                           required>
                    <div id="warn-year" class="text-danger small mt-1 d-none fw-semibold">
                        <i class="bi bi-exclamation-circle me-1"></i> Tahun tidak boleh minus!
                    </div>
                    @error('publication_year')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                {{-- Input Harga Buku: Min 0, cegah tombol minus --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold">Harga Buku (Rp)</label>
                    <input type="number" 
                           name="price" 
                           id="price"
                           class="form-control @error('price') is-invalid @enderror" 
                           placeholder="100000" 
                           value="{{ old('price', 100000) }}" 
                           min="0"
                           step="1000"
                           onkeydown="if(event.key==='-'||event.key==='e'||event.key==='E'||event.key==='+') event.preventDefault();"
                           oninput="checkNonNegative(this, 'warn-price', 'Harga buku tidak boleh minus!')"
                           required>
                    <div id="warn-price" class="text-danger small mt-1 d-none fw-semibold">
                        <i class="bi bi-exclamation-circle me-1"></i> Harga buku tidak boleh angka minus!
                    </div>
                    @error('price')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                {{-- Input Jumlah Eksemplar: Min 1, Max 20, cegah minus dan 0 --}}
                <div class="col-md-4">
                    <label class="form-label fw-semibold text-primary">Jumlah Eksemplar Fisik</label>
                    <input type="number" 
                           name="total_copies" 
                           id="total_copies"
                           class="form-control @error('total_copies') is-invalid @enderror" 
                           value="{{ old('total_copies', 1) }}" 
                           min="1" 
                           max="20"
                           onkeydown="if(event.key==='-'||event.key==='e'||event.key==='E'||event.key==='+') event.preventDefault();"
                           oninput="checkCopies(this)"
                           required>
                    <div id="warn-copies" class="text-danger small mt-1 d-none fw-semibold">
                        <i class="bi bi-exclamation-circle me-1"></i> Jumlah eksemplar minimal 1 dan tidak boleh minus!
                    </div>
                    @error('total_copies')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12">
                    <label class="form-label fw-semibold">Sampul Buku (Opsional)</label>
                    <input type="file" name="cover_image" class="form-control @error('cover_image') is-invalid @enderror" accept="image/*">
                    <small class="text-muted">Format: JPG, PNG, JPEG, WEBP (Maksimal 2MB)</small>
                    @error('cover_image')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12">
                    <label class="form-label fw-semibold">Deskripsi / Sinopsis</label>
                    <textarea name="description" rows="3" class="form-control @error('description') is-invalid @enderror">{{ old('description') }}</textarea>
                    @error('description')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>
                
                <div class="col-12 mt-4">
                    <button type="submit" id="btn-submit" class="btn btn-primary px-4 py-2">
                        Simpan Buku & Buat Barcode Eksemplar
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

{{-- Script Validasi Real-time Langsung di Layar Browser --}}
<script>
function checkNonNegative(input, warningId, message) {
    const warnEl = document.getElementById(warningId);
    const submitBtn = document.getElementById('btn-submit');
    
    if (parseFloat(input.value) < 0) {
        input.value = 0; // Otomatis reset ke 0 jika dipaste nilai minus
        warnEl.innerText = message;
        warnEl.classList.remove('d-none');
    } else {
        warnEl.classList.add('d-none');
    }
}

function checkCopies(input) {
    const warnEl = document.getElementById('warn-copies');
    const val = parseFloat(input.value);
    
    if (val < 1 || isNaN(val)) {
        warnEl.classList.remove('d-none');
        if (val < 0) input.value = 1; // Otomatis reset ke 1 jika minus
    } else if (val > 20) {
        input.value = 20; // Kunci batas atas sesuai atribut max
        warnEl.classList.add('d-none');
    } else {
        warnEl.classList.add('d-none');
    }
}
</script>
@endsection