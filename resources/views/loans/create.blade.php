@extends('layouts.app')

@section('title', 'Catat Peminjaman - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <h1 class="h3 fw-bold">Catat Transaksi Peminjaman</h1>
    <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary">Kembali</a>
</div>

<div class="card border-0 shadow-sm" style="max-width: 700px;">
    <div class="card-body p-4">
        <form action="{{ route('loans.store') }}" method="POST">
            @csrf
            <div class="mb-3">
                <label class="form-label fw-semibold">Pilih Anggota / Member</label>
                <select name="user_id" class="form-select" required>
                    <option value="">-- Pilih Member --</option>
                    @foreach($members as $member)
                        <option value="{{ $member->id }}">{{ $member->name }} ({{ $member->email }})</option>
                    @endforeach
                </select>
                <div class="form-text">Setiap member dibatasi maksimal meminjam 1 buku aktif.</div>
            </div>

            <div class="mb-3">
                <label class="form-label fw-semibold">Pilih Eksemplar Buku Fisik (Tersedia)</label>
                <select name="book_copy_id" class="form-select" required>
                    <option value="">-- Pilih Barcode Fisik Buku --</option>
                    @foreach($availableCopies as $copy)
                        <option value="{{ $copy->id }}">
                            [{{ $copy->copy_code }}] {{ $copy->book->title }} (Rp {{ number_format($copy->book->price, 0, ',', '.') }})
                        </option>
                    @endforeach
                </select>
                <div class="form-text">Hanya unit eksemplar berstatus 'Available' yang muncul pada daftar ini.</div>
            </div>

            <!-- Tambahkan input tanggal secara eksplisit agar masuk ke controller & database -->
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Tanggal Pinjam</label>
                    <input type="date" name="borrow_date" class="form-control" value="{{ date('Y-m-d') }}" required>
                </div>
                <div class="col-md-6 mb-3">
                    <label class="form-label fw-semibold">Tanggal Jatuh Tempo</label>
                    <input type="date" name="due_date" class="form-control" value="{{ date('Y-m-d', strtotime('+7 days')) }}" required>
                </div>
            </div>

            <div class="alert alert-info py-2 small mb-4">
                <i class="bi bi-info-circle me-1"></i> Durasi standar peminjaman otomatis ditetapkan <strong>7 Hari</strong> terhitung dari hari ini.
            </div>

            <button type="submit" class="btn btn-primary px-4">Simpan Peminjaman</button>
        </form>
    </div>
</div>
@endsection