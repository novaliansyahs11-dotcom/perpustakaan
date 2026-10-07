<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Riwayat Peminjaman - {{ $member->name }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-size: 13px; color: #212529; background: #fff; }
        .table th, .table td { padding: 6px 10px; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="p-4">

    <!-- Tombol Cetak / Kembali -->
    <div class="no-print d-flex justify-content-between align-items-center mb-4 pb-2 border-bottom">
        <button onclick="window.history.back()" class="btn btn-outline-secondary btn-sm">&larr; Kembali</button>
        <button onclick="window.print()" class="btn btn-primary btn-sm">
            Cetak / Simpan PDF
        </button>
    </div>

    <!-- Kop Surat Laporan -->
    <div class="text-center mb-4 pb-3 border-bottom border-2">
        <h4 class="fw-bold mb-0 text-uppercase tracking-wide">PERPUSTAKAAN PERPUSAPP</h4>
        <p class="text-muted mb-0 small">Sistem Informasi Pengelolaan Sirkulasi Koleksi Buku & Anggota</p>
        <h5 class="fw-bold mt-3 text-decoration-underline">KARTU LAPORAN RIWAYAT PEMINJAMAN ANGGOTA</h5>
    </div>

    <!-- Data Identitas Anggota -->
    <div class="row g-2 mb-4">
        <div class="col-6">
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-muted" style="width: 140px;">Nama Anggota</td>
                    <td class="fw-bold">: {{ $member->name }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Alamat Email</td>
                    <td>: {{ $member->email }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Nomor WhatsApp</td>
                    <td>: {{ $member->phone ?? '-' }}</td>
                </tr>
            </table>
        </div>
        <div class="col-6">
            <table class="table table-sm table-borderless mb-0">
                <tr>
                    <td class="text-muted" style="width: 140px;">Nomor Identitas</td>
                    <td class="fw-bold">: {{ strtoupper($member->id_card_type ?? 'KTM') }} - {{ $member->id_card_number ?? '-' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Status Akun</td>
                    <td>: {{ $member->is_active ? 'Aktif' : 'Nonaktif' }}</td>
                </tr>
                <tr>
                    <td class="text-muted">Tgl Cetak Laporan</td>
                    <td>: {{ date('d F Y, H:i') }} WIB</td>
                </tr>
            </table>
        </div>
    </div>

    <!-- Status Ringkasan Tanggungan -->
    <div class="alert {{ $activeLoansCount > 0 ? 'alert-warning' : 'alert-success' }} py-2 px-3 mb-4">
        <strong>Status Tanggungan Saat Ini:</strong> 
        {{ $activeLoansCount > 0 ? "Memiliki {$activeLoansCount} buku yang belum dikembalikan." : "Bebas Pustaka / Tidak memiliki pinjaman aktif." }}
        | <strong>Total Akumulasi Denda:</strong> Rp {{ number_format($totalFines, 0, ',', '.') }}
    </div>

    <!-- Tabel Rekapitulasi Riwayat Transaksi -->
    <table class="table table-bordered align-middle">
        <thead class="table-light text-center">
            <tr>
                <th style="width: 40px;">No</th>
                <th>Judul Buku</th>
                <th style="width: 120px;">Kode Eksemplar</th>
                <th style="width: 110px;">Tgl Pinjam</th>
                <th style="width: 110px;">Jatuh Tempo</th>
                <th style="width: 110px;">Tgl Kembali</th>
                <th style="width: 90px;">Denda</th>
                <th style="width: 110px;">Status</th>
            </tr>
        </thead>
        <tbody>
            @forelse($loans as $index => $loan)
            <tr>
                <td class="text-center">{{ $index + 1 }}</td>
                <td>
                    <strong>{{ $loan->bookCopy->book->title ?? '-' }}</strong><br>
                    <small class="text-muted">Penulis: {{ $loan->bookCopy->book->author ?? '-' }}</small>
                </td>
                <td class="text-center font-monospace">{{ $loan->bookCopy->copy_code ?? '-' }}</td>
                <td class="text-center">{{ $loan->borrow_date }}</td>
                <td class="text-center">{{ $loan->due_date }}</td>
                <td class="text-center">{{ $loan->return_date ?? '-' }}</td>
                <td class="text-end">
                    {{ $loan->fine_amount > 0 ? 'Rp ' . number_format($loan->fine_amount, 0, ',', '.') : '-' }}
                </td>
                <td class="text-center">
                    {{ $loan->status === 'returned' ? 'Dikembalikan' : 'Sedang Dipinjam' }}
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="8" class="text-center py-3 text-muted">Belum ada riwayat transaksi peminjaman.</td>
            </tr>
            @endforelse
        </tbody>
    </table>

    <!-- Tanda Tangan Pengesahan -->
    <div class="row mt-5 pt-3">
        <div class="col-7"></div>
        <div class="col-5 text-center">
            <p class="mb-5">Petugas Layanan Perpustakaan,</p>
            <p class="fw-bold mb-0 text-decoration-underline">{{ auth()->user()->role === 'admin' ? auth()->user()->name : 'Petugas Sirkulasi' }}</p>
            <small class="text-muted">NIP/ID: {{ auth()->user()->id_card_number ?? '19900101-PERPUS' }}</small>
        </div>
    </div>

</body>
</html>