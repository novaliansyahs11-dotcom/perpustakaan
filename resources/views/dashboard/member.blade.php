@extends('layouts.app')

@section('title', 'Dashboard Member - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold mb-0">Selamat Datang, {{ Auth::user()->name }}!</h1>
        <small class="text-muted">Pantau status peminjaman buku perpustakaan Anda di sini.</small>
    </div>
    <a href="{{ route('member.browse') }}" class="btn btn-primary">
        <i class="bi bi-search me-1"></i> Jelajahi Buku
    </a>
</div>

<!-- Notifikasi & Status Pinjaman Aktif -->
@if($myActiveLoans->count() > 0)
    @php
        $currentLoan = $myActiveLoans->first();
        $isOverdue = \Carbon\Carbon::now()->toDateString() > $currentLoan->due_date;
        $daysLeft = \Carbon\Carbon::now()->diffInDays(\Carbon\Carbon::parse($currentLoan->due_date), false);
    @endphp
    <div class="alert {{ $isOverdue ? 'alert-danger' : 'alert-info' }} shadow-sm p-4 mb-4" role="alert">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="fw-bold mb-1">
                    <i class="bi bi-book-half me-2"></i>Buku yang Sedang Anda Pinjam: {{ $currentLoan->bookCopy->book->title }}
                </h5>
                <p class="mb-0">
                    Kode Eksemplar Fisik: <code>{{ $currentLoan->bookCopy->copy_code }}</code> | Tanggal Pinjam: {{ $currentLoan->borrow_date }}
                </p>
                @if($isOverdue)
                    <span class="badge bg-danger mt-2">TELAT PENGEMBALIAN! Jatuh tempo pada: {{ $currentLoan->due_date }}. Segera kembalikan ke perpustakaan.</span>
                @else
                    <span class="badge bg-success mt-2">Batas Pengembalian: {{ $currentLoan->due_date }} (Sisa {{ (int)$daysLeft }} hari lagi)</span>
                @endif
            </div>
            <a href="{{ route('member.loans') }}" class="btn btn-outline-dark btn-sm">Lihat Detail Pinjaman</a>
        </div>
    </div>
@else
    <div class="card border-0 shadow-sm p-4 mb-4 bg-light">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="fw-bold text-success mb-1"><i class="bi bi-check-circle me-2"></i>Bebas Tanggungan</h5>
                <p class="text-muted mb-0">Anda sedang tidak meminjam buku apapun. Kuota peminjaman Anda: <strong>1 Buku</strong>.</p>
            </div>
            <a href="{{ route('member.browse') }}" class="btn btn-outline-success">Pinjam Buku Sekarang</a>
        </div>
    </div>
@endif

<!-- Riwayat Terakhir -->
<div class="card border-0 shadow-sm">
    <div class="card-header bg-white py-3">
        <h5 class="fw-bold mb-0">5 Riwayat Peminjaman Terakhir</h5>
    </div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3">Judul Buku</th>
                        <th>Kode Eksemplar</th>
                        <th>Tgl Pinjam</th>
                        <th>Tgl Dikembalikan</th>
                        <th>Denda</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($myLoanHistory as $history)
                    <tr>
                        <td class="ps-3 fw-semibold">{{ $history->bookCopy->book->title }}</td>
                        <td><code>{{ $history->bookCopy->copy_code }}</code></td>
                        <td>{{ $history->borrow_date }}</td>
                        <td>{{ $history->return_date ?? '-' }}</td>
                        <td>
                            @if($history->fine_amount > 0)
                                <span class="badge bg-danger">Rp {{ number_format($history->fine_amount, 0, ',', '.') }}</span>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td><span class="badge bg-success">Dikembalikan</span></td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada riwayat buku yang dikembalikan.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection