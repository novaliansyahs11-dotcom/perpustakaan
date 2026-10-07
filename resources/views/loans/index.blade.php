@extends('layouts.app')

@section('title', 'Sirkulasi Peminjaman - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold mb-0">Sirkulasi Peminjaman & Pengembalian</h1>
        <small class="text-muted">Pencatatan sirkulasi buku fisik, pengembalian, dan denda keterlambatan.</small>
    </div>
    <div class="d-flex gap-2">
        {{-- Tombol Scan Tiket QR Mandiri (Fast-Pass 60 Menit) --}}
        <a href="{{ route('loans.scan-view') }}" class="btn btn-outline-primary">
            <i class="bi bi-qr-code-scan me-1"></i> Scan Tiket QR
        </a>
        <a href="{{ route('loans.report') }}" target="_blank" class="btn btn-outline-danger">
            <i class="bi bi-printer me-1"></i> Cetak Laporan
        </a>
        <a href="{{ route('loans.create') }}" class="btn btn-primary">
            <i class="bi bi-plus-circle me-1"></i> Catat Peminjaman Baru
        </a>
    </div>
</div>

{{-- Flash Alert Notifikasi --}}
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
        <i class="bi bi-info-circle-fill me-2"></i> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

{{-- Form Pencarian Data Sirkulasi --}}
<form action="{{ route('loans.index') }}" method="GET" class="card border-0 shadow-sm p-3 mb-4">
    <div class="row g-2">
        <div class="col-md-10">
            <div class="input-group">
                <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                <input type="text" name="search" class="form-control" 
                       placeholder="Cari nama member peminjam, nomor HP, judul buku, atau kode barcode eksemplar..." 
                       value="{{ request('search') }}">
            </div>
        </div>
        <div class="col-md-2">
            <button type="submit" class="btn btn-primary w-100">
                <i class="bi bi-search me-1"></i> Cari
            </button>
        </div>
    </div>
</form>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 50px;">No</th>
                        <th>Member Peminjam</th>
                        <th>Buku & Kode Fisik</th>
                        <th>Tgl Pinjam</th>
                        <th>Jatuh Tempo</th>
                        <th>Tgl Kembali</th>
                        <th>Denda</th>
                        <th>Status</th>
                        <th class="text-center" style="width: 140px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $index => $loan)
                    @php
                        $isOverdue = false;
                        $estimatedFine = 0;
                        $daysLate = 0;

                        if ($loan->status === 'borrowed') {
                            $today = \Carbon\Carbon::now()->startOfDay();
                            $dueDate = \Carbon\Carbon::parse($loan->due_date)->startOfDay();

                            if ($today->gt($dueDate)) {
                                $isOverdue = true;
                                $daysLate = $today->diffInDays($dueDate);
                                $bookPrice = $loan->bookCopy->book->price ?? 0;

                                // Aturan denda mingguan progresif
                                if ($daysLate <= 7) {
                                    $estimatedFine = $bookPrice * 0.10; // 10%
                                } elseif ($daysLate <= 14) {
                                    $estimatedFine = $bookPrice * 0.20; // 20%
                                } else {
                                    $estimatedFine = $bookPrice * 0.30; // 30%
                                }

                                // Plafon maksimal 100% dari harga buku acuan
                                if ($estimatedFine > $bookPrice) {
                                    $estimatedFine = $bookPrice;
                                }
                            }
                        }
                    @endphp
                    <tr>
                        <td class="ps-3">{{ $loans->firstItem() + $index }}</td>
                        <td>
                            <strong class="d-block">{{ $loan->user->name }}</strong>
                            <small class="text-muted">{{ $loan->user->phone ?? $loan->user->email }}</small>
                        </td>
                        <td>
                            <span class="fw-semibold">{{ $loan->bookCopy->book->title }}</span><br>
                            <code class="text-primary">{{ $loan->bookCopy->copy_code }}</code>
                        </td>
                        <td>{{ $loan->borrow_date }}</td>
                        <td>
                            @if($loan->status === 'borrowed' && $isOverdue)
                                <strong class="text-danger">{{ $loan->due_date }}</strong>
                            @else
                                {{ $loan->due_date }}
                            @endif
                        </td>
                        <td>{{ $loan->return_date ?? '-' }}</td>
                        <td>
                            @if($loan->status === 'returned')
                                @if($loan->fine_amount > 0)
                                    <!-- Cetak Invoice Denda Riwayat -->
                                    <a href="{{ route('loans.fine-receipt', $loan->id) }}" target="_blank" 
                                       class="badge bg-danger text-decoration-none shadow-sm" 
                                       title="Klik untuk Cetak Invoice Denda">
                                        <i class="bi bi-printer me-1"></i> Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}
                                    </a>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            @else
                                {{-- Jika masih dipinjam dan terlambat --}}
                                @if($isOverdue)
                                    <div>
                                        <a href="{{ route('loans.fine-receipt', $loan->id) }}" target="_blank" 
                                           class="badge bg-danger text-decoration-none shadow-sm" 
                                           title="Klik untuk Cetak Invoice Denda">
                                            <i class="bi bi-receipt me-1"></i> Rp {{ number_format($estimatedFine, 0, ',', '.') }}
                                        </a>
                                        <small class="d-block text-danger fw-semibold mt-1" style="font-size: 0.75rem;">
                                            (Telat {{ $daysLate }} hari)
                                        </small>
                                    </div>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            @endif
                        </td>
                        <td>
                            @if($loan->status === 'borrowed')
                                @if($isOverdue)
                                    <span class="badge bg-danger">Terlambat</span>
                                @else
                                    <span class="badge bg-warning text-dark">Dipinjam</span>
                                @endif
                            @else
                                <span class="badge bg-success">Dikembalikan</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @if($loan->status === 'borrowed')
                                <form action="{{ route('loans.return', $loan->id) }}" method="POST" class="d-inline" 
                                    onsubmit="return confirm('{{ $isOverdue ? "Buku terlambat " . $daysLate . " hari. Total denda yang harus dibayar: Rp " . number_format($estimatedFine, 0, ",", ".") . ". Konfirmasi pengembalian?" : "Konfirmasi pengembalian buku ini?" }}');">
                                    @csrf
                                    <button type="submit" class="btn btn-sm {{ $isOverdue ? 'btn-danger' : 'btn-success' }}">
                                        <i class="bi bi-check-circle me-1"></i> Kembalikan
                                    </button>
                                </form>
                            @else
                                <span class="text-muted small"><i class="bi bi-check2-all text-success"></i> Selesai</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="9" class="text-center py-4 text-muted">Belum ada catatan transaksi sirkulasi.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-3">
        {{ $loans->links('pagination::bootstrap-5') }}
    </div>
</div>
@endsection