@extends('layouts.app')

@section('title', 'Admin Dashboard - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold mb-0">Dashboard Administrator</h1>
        <small class="text-muted">Statistik menyeluruh operasional dan inventaris perpustakaan.</small>
    </div>
    <span class="badge bg-primary px-3 py-2 fs-6 shadow-sm">
        <i class="bi bi-person-badge me-1"></i> Role: Petugas
    </span>
</div>

<!-- Baris 1: 4 Kartu Utama Inventaris Koleksi Fisik -->
<div class="row g-3 mb-3">
    <!-- 1. Total Buku -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white h-100" style="background-color: #0d6efd; border-radius: 8px;">
            <div class="card-body p-3">
                <small class="text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.75rem;">TOTAL JUDUL BUKU</small>
                <h2 class="display-6 fw-bold mb-0 mt-2">{{ $totalBooks }}</h2>
                <small class="text-white-50">Master judul terdaftar</small>
            </div>
        </div>
    </div>
    <!-- 2. Total Eksemplar -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-white h-100" style="background-color: #198754; border-radius: 8px;">
            <div class="card-body p-3">
                <small class="text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.75rem;">TOTAL EKSEMPLAR</small>
                <h2 class="display-6 fw-bold mb-0 mt-2">{{ $totalCopies }}</h2>
                <small class="text-white-50">Buku fisik di rak</small>
            </div>
        </div>
    </div>
    <!-- 3. Buku Tersedia -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-dark h-100" style="background-color: #20c997; border-radius: 8px;">
            <div class="card-body p-3 text-white">
                <small class="text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.75rem;">BUKU TERSEDIA</small>
                <h2 class="display-6 fw-bold mb-0 mt-2">{{ $availableCopies }}</h2>
                <small class="text-white-50">Siap untuk dipinjam</small>
            </div>
        </div>
    </div>
    <!-- 4. Buku Dipinjam -->
    <div class="col-md-3">
        <div class="card border-0 shadow-sm text-dark h-100" style="background-color: #ffc107; border-radius: 8px;">
            <div class="card-body p-3">
                <small class="text-uppercase fw-semibold" style="letter-spacing: 0.5px; font-size: 0.75rem;">BUKU DIPINJAM</small>
                <h2 class="display-6 fw-bold mb-0 mt-2">{{ $borrowedCopies }}</h2>
                <small class="text-dark-50">Sedang di tangan peminjam</small>
            </div>
        </div>
    </div>
</div>

<!-- Baris 2: 3 Kartu Aktivitas Member, Sirkulasi, & Denda -->
<div class="row g-3 mb-4">
    <!-- 5. Member Aktif -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100 border-start border-info border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">MEMBER AKTIF</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $activeMembers }}</h3>
                    <small class="text-success"><i class="bi bi-check-circle me-1"></i>Anggota terverifikasi aktif</small>
                </div>
                <div class="fs-1 text-info"><i class="bi bi-people"></i></div>
            </div>
        </div>
    </div>
    <!-- 6. Total Transaksi -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100 border-start border-primary border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">TOTAL TRANSAKSI</span>
                    <h3 class="fw-bold mb-0 text-dark">{{ $totalTransactions }}</h3>
                    <small class="text-muted">Peminjaman & pengembalian</small>
                </div>
                <div class="fs-1 text-primary"><i class="bi bi-arrow-left-right"></i></div>
            </div>
        </div>
    </div>
    <!-- 7. Total Denda -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm p-3 h-100 border-start border-danger border-4">
            <div class="d-flex justify-content-between align-items-center">
                <div>
                    <span class="text-muted small text-uppercase fw-semibold">TOTAL DENDA</span>
                    <h3 class="fw-bold mb-0 text-danger">Rp {{ number_format($totalFines, 0, ',', '.') }}</h3>
                    <small class="text-muted">Lunas: Rp {{ number_format($settledFines, 0, ',', '.') }} | Berjalan: Rp {{ number_format($pendingFines, 0, ',', '.') }}</small>
                </div>
                <div class="fs-1 text-danger"><i class="bi bi-cash-stack"></i></div>
            </div>
        </div>
    </div>
</div>

<!-- Baris 3: 8. Buku Paling Banyak Dipinjam & 9. Statistik Peminjaman Bulanan -->
<div class="row g-4 mb-4">
    <!-- 9. Statistik Peminjaman (Bar Chart) -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-bar-chart-line me-2 text-primary"></i>Statistik Tren Peminjaman</h6>
            </div>
            <div class="card-body">
                <canvas id="loanTrendChart" height="200"></canvas>
            </div>
        </div>
    </div>

    <!-- 8. Buku Paling Banyak Dipinjam -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-trophy me-2 text-warning"></i>Buku Paling Banyak Dipinjam</h6>
            </div>
            <div class="card-body p-0">
                <ul class="list-group list-group-flush">
                    @forelse($mostBorrowedBooks as $rank => $item)
                    <li class="list-group-item d-flex align-items-center p-3">
                        <span class="badge {{ $rank == 0 ? 'bg-warning text-dark' : 'bg-light text-dark border' }} rounded-circle me-3" style="width: 28px; height: 28px; line-height: 20px;">
                            {{ $rank + 1 }}
                        </span>
                        <div class="flex-grow-1 text-truncate pe-2">
                            <h6 class="mb-0 fw-bold text-truncate">{{ $item->title }}</h6>
                            <small class="text-muted">{{ $item->author }}</small>
                        </div>
                        <span class="badge bg-primary rounded-pill">{{ $item->total_loans }}x Pinjam</span>
                    </li>
                    @empty
                    <li class="list-group-item text-center text-muted py-4">Belum ada catatan peminjaman buku.</li>
                    @endforelse
                </ul>
            </div>
        </div>
    </div>
</div>

<!-- Baris 4: Distribusi Kategori Buku & Aktivitas Terkini -->
<div class="row g-4">
    <div class="col-md-5">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3">
                <h6 class="fw-bold mb-0"><i class="bi bi-pie-chart me-2 text-primary"></i>Distribusi Kategori Buku</h6>
            </div>
            <div class="card-body">
                <div style="height: 240px; position: relative;">
                    <canvas id="categoryChart"></canvas>
                </div>
            </div>
        </div>
    </div>

    <div class="col-md-7">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                <h6 class="fw-bold mb-0"><i class="bi bi-clock-history me-2 text-primary"></i>Aktivitas Peminjaman Terkini</h6>
                <a href="{{ route('loans.index') }}" class="btn btn-sm btn-outline-primary">Lihat Sirkulasi</a>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-3">Peminjam</th>
                                <th>Buku</th>
                                <th>Tgl Pinjam</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($recentLoans as $loan)
                            <tr>
                                <td class="ps-3 fw-semibold">{{ $loan->user->name }}</td>
                                <td>{{ Str::limit($loan->bookCopy->book->title, 28) }}</td>
                                <td>{{ $loan->borrow_date }}</td>
                                <td>
                                    @if($loan->status === 'borrowed')
                                        <span class="badge bg-warning text-dark">Dipinjam</span>
                                    @else
                                        <span class="badge bg-success">Dikembalikan</span>
                                    @endif
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="text-center py-4 text-muted">Belum ada transaksi peminjaman.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // 9. Statistik Peminjaman Bulanan
    const ctxTrend = document.getElementById('loanTrendChart').getContext('2d');
    new Chart(ctxTrend, {
        type: 'bar',
        data: {
            labels: {!! json_encode($monthlyLabels) !!},
            datasets: [{
                label: 'Jumlah Transaksi',
                data: {!! json_encode($monthlyData) !!},
                backgroundColor: 'rgba(13, 110, 253, 0.85)',
                borderColor: '#0d6efd',
                borderRadius: 4,
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: { precision: 0 }
                }
            }
        }
    });

    // Distribusi Kategori Buku (Donut Chart)
    const ctxCat = document.getElementById('categoryChart').getContext('2d');
    new Chart(ctxCat, {
        type: 'doughnut',
        data: {
            labels: {!! json_encode($categoryLabels) !!},
            datasets: [{
                data: {!! json_encode($categoryCounts) !!},
                backgroundColor: [
                    '#0d6efd', '#198754', '#ffc107', '#0dcaf0', '#6c757d', '#d63384', '#6f42c1', '#fd7e14'
                ],
                borderWidth: 2
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: {
                    position: 'bottom',
                    labels: { boxWidth: 12, padding: 10 }
                }
            }
        }
    });
</script>
@endsection