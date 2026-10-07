<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Rekapitulasi Sirkulasi Perpustakaan</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; font-size: 12px; }
        @media print {
            @page { size: landscape; margin: 15mm; }
            .no-print { display: none !important; }
        }
        .table th, .table td { vertical-align: middle; padding: 6px 8px; }
    </style>
</head>
<body class="bg-white p-4">

    <!-- Tombol Aksi Layar -->
    <div class="no-print d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <div>
            <h5 class="mb-0 fw-bold">Pratinjau Laporan Rekapitulasi Sirkulasi</h5>
            <small class="text-muted">Siap dicetak atau disimpan sebagai PDF (Rekomendasi orientasi: Landscape).</small>
        </div>
        <div class="d-flex gap-2">
            <button onclick="window.print()" class="btn btn-primary btn-sm">
                Cetak / Simpan PDF
            </button>
            <button onclick="window.close()" class="btn btn-outline-secondary btn-sm">
                Tutup Halaman
            </button>
        </div>
    </div>

    <!-- Kop / Judul Laporan -->
    <div class="text-center mb-4">
        <h3 class="fw-bold mb-1">LAPORAN REKAPITULASI SIRKULASI PERPUSTAKAAN</h3>
        <p class="text-muted mb-0">Dicetak pada tanggal: {{ \Carbon\Carbon::now()->translatedFormat('d F Y, H:i') }} WIB</p>
    </div>

    @php
        $totalDendaTerkumpul = 0;
        $totalDendaBerjalan = 0;
        $totalBukuDipinjam = 0;
        $totalBukuKembali = 0;
    @endphp

    <!-- Tabel Data Sirkulasi -->
    <table class="table table-bordered border-dark table-striped align-middle mb-4">
        <thead class="table-dark text-center">
            <tr>
                <th style="width: 40px;">No</th>
                <th>Member Peminjam</th>
                <th>Judul Buku</th>
                <th>Kode Barcode</th>
                <th>Tgl Pinjam</th>
                <th>Jatuh Tempo</th>
                <th>Tgl Kembali</th>
                <th>Denda</th>
                <th>Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loans as $index => $loan)
            @php
                $fineText = '-';
                $statusText = 'Dipinjam';
                $statusBadgeClass = 'text-warning';

                if ($loan->status === 'returned') {
                    $totalBukuKembali++;
                    $statusText = 'Dikembalikan';
                    $statusBadgeClass = 'text-success';
                    if ($loan->fine_amount > 0) {
                        $fineText = 'Rp ' . number_format($loan->fine_amount, 0, ',', '.');
                        $totalDendaTerkumpul += $loan->fine_amount;
                    }
                } else {
                    $totalBukuDipinjam++;
                    // Perhitungan denda berjalan jika terlambat
                    $today = \Carbon\Carbon::now()->startOfDay();
                    $dueDate = \Carbon\Carbon::parse($loan->due_date)->startOfDay();

                    if ($today->gt($dueDate)) {
                        $statusText = 'Terlambat';
                        $statusBadgeClass = 'text-danger font-weight-bold';
                        $daysLate = $today->diffInDays($dueDate);
                        $bookPrice = $loan->bookCopy->book->price ?? 0;

                        if ($daysLate <= 7) {
                            $calc = $bookPrice * 0.10;
                        } elseif ($daysLate <= 14) {
                            $calc = $bookPrice * 0.20;
                        } else {
                            $calc = $bookPrice * 0.30;
                        }

                        $fineText = 'Rp ' . number_format($calc, 0, ',', '.') . ' (Telat ' . $daysLate . ' hr)';
                        $totalDendaBerjalan += $calc;
                    }
                }
            @endphp
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $loan->user->name }}</strong><br>
                    <small class="text-muted">{{ $loan->user->phone ?? $loan->user->email }}</small>
                </td>
                <td>{{ $loan->bookCopy->book->title }}</td>
                <td class="text-center font-monospace">{{ $loan->bookCopy->copy_code }}</td>
                <td class="text-center">{{ $loan->borrow_date }}</td>
                <td class="text-center">{{ $loan->due_date }}</td>
                <td class="text-center">{{ $loan->return_date ?? '-' }}</td>
                <td class="text-end fw-semibold {{ $loan->status === 'borrowed' && $fineText !== '-' ? 'text-danger' : '' }}">
                    {{ $fineText }}
                </td>
                <td class="text-center fw-bold {{ $statusBadgeClass }}">
                    {{ $statusText }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="9" class="text-center py-3 text-muted">Belum ada rekaman sirkulasi.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Ringkasan Statistik Laporan -->
    <div class="row g-3">
        <div class="col-6">
            <div class="border border-dark p-3 rounded">
                <h6 class="fw-bold mb-2">Ringkasan Sirkulasi:</h6>
                <div class="d-flex justify-content-between mb-1">
                    <span>Total Seluruh Transaksi:</span>
                    <strong>{{ $loans->count() }} Transaksi</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>Buku Sedang Dipinjam:</span>
                    <strong>{{ $totalBukuDipinjam }} Eksemplar</strong>
                </div>
                <div class="d-flex justify-content-between">
                    <span>Buku Telah Selesai Dikembalikan:</span>
                    <strong>{{ $totalBukuKembali }} Eksemplar</strong>
                </div>
            </div>
        </div>
        <div class="col-6">
            <div class="border border-dark p-3 rounded bg-light">
                <h6 class="fw-bold mb-2">Rekapitulasi Keuangan Denda:</h6>
                <div class="d-flex justify-content-between mb-1">
                    <span>Denda Telah Dibayar (Lunas):</span>
                    <strong class="text-success">Rp {{ number_format($totalDendaTerkumpul, 0, ',', '.') }}</strong>
                </div>
                <div class="d-flex justify-content-between mb-1">
                    <span>Potensi Denda Keterlambatan Berjalan:</span>
                    <strong class="text-danger">Rp {{ number_format($totalDendaBerjalan, 0, ',', '.') }}</strong>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-between">
                    <span class="fw-bold">Total Nilai Denda:</span>
                    <strong class="fw-bold">Rp {{ number_format($totalDendaTerkumpul + $totalDendaBerjalan, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>
    </div>

    <!-- Tanda Tangan Pengesahan -->
    <div class="d-flex justify-content-end mt-5 pt-3">
        <div class="text-center" style="width: 220px;">
            <p class="mb-5">Petugas Perpustakaan,</p>
            <p class="fw-bold mb-0 text-decoration-underline">{{ Auth::user()->name ?? 'Administrator' }}</p>
            <small class="text-muted">PerpusApp System</small>
        </div>
    </div>

</body>
</html>