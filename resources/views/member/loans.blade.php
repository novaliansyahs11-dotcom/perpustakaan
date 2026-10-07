@extends('layouts.app')

@section('title', 'Riwayat Pinjaman Saya - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold mb-0">Riwayat Pinjaman Saya</h1>
        <small class="text-muted">Daftar seluruh transaksi sirkulasi buku fisik dan status denda Anda.</small>
    </div>
    <!-- Tombol Cetak Rekapitulasi Riwayat Mandiri -->
    <a href="{{ route('member.loans.print') }}" target="_blank" class="btn btn-outline-primary shadow-sm">
        <i class="bi bi-printer me-1"></i> Cetak Riwayat Saya
    </a>
</div>

<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 50px;">No</th>
                        <th>Judul Buku</th>
                        <th>Kode Eksemplar</th>
                        <th>Tgl Pinjam</th>
                        <th>Batas Waktu</th>
                        <th>Tgl Dikembalikan</th>
                        <th>Denda</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($loans as $index => $loan)
                    @php
                        $today = \Carbon\Carbon::now()->startOfDay();
                        $dueDate = \Carbon\Carbon::parse($loan->due_date)->startOfDay();
                        $isOverdue = $today->gt($dueDate) && $loan->status === 'borrowed';
                        
                        // Hitung estimasi denda jika sedang telat berjalan
                        $estimatedFine = 0;
                        $daysLate = 0;
                        if ($isOverdue) {
                            $daysLate = $today->diffInDays($dueDate);
                            $bookPrice = $loan->bookCopy->book->price ?? 0;
                            if ($daysLate <= 7) {
                                $estimatedFine = $bookPrice * 0.10;
                            } elseif ($daysLate <= 14) {
                                $estimatedFine = $bookPrice * 0.20;
                            } else {
                                $estimatedFine = $bookPrice * 0.30;
                            }
                        }
                    @endphp
                    <tr>
                        <td class="ps-3">{{ $loans->firstItem() + $index }}</td>
                        <td>
                            <strong class="d-block text-dark">{{ $loan->bookCopy->book->title ?? '-' }}</strong>
                            <small class="text-muted">Penulis: {{ $loan->bookCopy->book->author ?? '-' }}</small>
                        </td>
                        <td><code>{{ $loan->bookCopy->copy_code ?? '-' }}</code></td>
                        <td>{{ $loan->borrow_date }}</td>
                        <td>
                            <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                {{ $loan->due_date }}
                            </span>
                            @if($isOverdue)
                                <small class="text-danger d-block fw-semibold" style="font-size: 11px;">
                                    (Telat {{ $daysLate }} hari)
                                </small>
                            @endif
                        </td>
                        <td>{{ $loan->return_date ?? '-' }}</td>
                        <td>
                            @if($loan->fine_amount > 0)
                                <!-- Klik badge untuk cetak slip nota denda transaksi ini -->
                                <a href="{{ route('loans.fine-receipt', $loan->id) }}" target="_blank" class="badge bg-danger text-decoration-none shadow-sm" title="Klik untuk Cetak Slip Nota Denda">
                                    <i class="bi bi-printer me-1"></i> Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}
                                </a>
                            @elseif($isOverdue && $estimatedFine > 0)
                                <a href="{{ route('loans.fine-receipt', $loan->id) }}" target="_blank" class="badge bg-danger text-decoration-none shadow-sm" title="Klik untuk Cetak Slip Tagihan Denda">
                                    <i class="bi bi-receipt me-1"></i> Rp {{ number_format($estimatedFine, 0, ',', '.') }}
                                    <small class="d-block" style="font-size: 9px;">(Berjalan)</small>
                                </a>
                            @else
                                <span class="text-muted">-</span>
                            @endif
                        </td>
                        <td>
                            @if($loan->status === 'borrowed')
                                @if($isOverdue)
                                    <span class="badge bg-danger">Terlambat</span>
                                @else
                                    <span class="badge bg-warning text-dark">Dipinjam</span>
                                @endif
                            @elseif($loan->status === 'returned')
                                <span class="badge bg-success">Dikembalikan</span>
                            @else
                                <span class="badge bg-secondary">{{ ucfirst($loan->status) }}</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="text-center py-4 text-muted">Belum ada riwayat peminjaman buku.</td>
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