<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Nota Tagihan Denda - #LN-{{ str_pad($loan->id, 5, '0', STR_PAD_LEFT) }}</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { font-size: 13px; color: #212529; background: #f4f6f9; }
        .receipt-card { max-width: 680px; margin: 30px auto; background: #fff; padding: 35px; border-radius: 10px; box-shadow: 0 4px 15px rgba(0,0,0,0.06); }
        .border-dashed { border-top: 2px dashed #dee2e6; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            .receipt-card { box-shadow: none; padding: 0; margin: 0 auto; max-width: 100%; }
        }
    </style>
</head>
<body>

    <div class="receipt-card">
        <!-- Tombol Aksi Layar -->
        <div class="no-print d-flex justify-content-between align-items-center mb-4 pb-3 border-bottom">
            @php
                $backUrl = auth()->user()->role === 'admin' ? route('loans.index') : route('member.loans');
            @endphp
            <a href="{{ $backUrl }}" class="btn btn-outline-secondary btn-sm">
                <i class="bi bi-arrow-left me-1"></i> Kembali
            </a>
            <button onclick="window.print()" class="btn btn-danger btn-sm shadow-sm">
                <i class="bi bi-printer me-1"></i> Cetak / Simpan PDF
            </button>
        </div>

        <!-- Header Nota -->
        <div class="d-flex justify-content-between align-items-start pb-3 border-bottom">
            <div>
                <h4 class="fw-bold mb-1 text-primary"><i class="bi bi-book-half me-1"></i> PERPUSAPP</h4>
                <p class="text-muted small mb-0">Layanan Perpustakaan & Sirkulasi Buku</p>
            </div>
            <div class="text-end">
                <span class="badge bg-danger fs-6 px-3 py-2 text-uppercase">INVOICE</span>
                <div class="font-monospace small text-muted mt-1">NO: #DENDA-{{ date('Ymd') }}-{{ $loan->id }}</div>
            </div>
        </div>

        <!-- Rincian Peminjam & Transaksi -->
        <div class="row g-3 my-3">
            <div class="col-6">
                <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 10px;">Identitas Peminjam</small>
                <div class="fw-bold fs-6">{{ $loan->user->name }}</div>
                <div class="text-muted small">{{ $loan->user->email }}</div>
                <div class="text-muted small">{{ $loan->user->phone ?? '-' }}</div>
                <span class="badge bg-light text-dark border text-uppercase mt-1">
                    {{ $loan->user->id_card_type ?? 'KTM' }}: {{ $loan->user->id_card_number ?? '-' }}
                </span>
            </div>
            <div class="col-6 text-end">
                <small class="text-muted text-uppercase fw-semibold d-block" style="font-size: 10px;">Jadwal Sirkulasi</small>
                <div class="small">Tgl Pinjam: <strong>{{ $loan->borrow_date }}</strong></div>
                <div class="small text-danger">Jatuh Tempo: <strong>{{ $loan->due_date }}</strong></div>
                <div class="small">Tgl Kembali: <strong>{{ $loan->return_date ?? 'Belum Dikembalikan' }}</strong></div>
                <div class="small text-muted mt-1">Status: <span class="badge {{ $loan->status === 'returned' ? 'bg-success' : 'bg-warning text-dark' }}">{{ ucfirst($loan->status) }}</span></div>
            </div>
        </div>

        <!-- Rincian Perhitungan Denda -->
        <table class="table table-bordered align-middle mt-4">
            <thead class="table-light">
                <tr>
                    <th>Deskripsi Item</th>
                    <th class="text-center" style="width: 140px;">Rincian Nilai</th>
                    <th class="text-end" style="width: 140px;">Jumlah</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <strong class="text-dark">{{ $loan->bookCopy->book->title }}</strong>
                        <div class="text-muted small">Kode Eksemplar: <code>{{ $loan->bookCopy->copy_code }}</code></div>
                        <div class="text-muted small">Penulis: {{ $loan->bookCopy->book->author }}</div>
                    </td>
                    <td class="text-center">
                        <small class="text-muted d-block">Harga Resmi Buku:</small>
                        Rp {{ number_format($bookPrice, 0, ',', '.') }}
                    </td>
                    <td class="text-end fw-semibold">-</td>
                </tr>
                <tr>
                    <td colspan="2">
                        <strong>Denda Keterlambatan Pengembalian</strong>
                        <div class="small text-muted">
                            Keterlambatan: <strong class="text-danger">{{ $daysLate }} Hari</strong> 
                            @if($daysLate > 0)
                                (Kategori {{ $daysLate <= 7 ? 'Hari 1–7 = 10%' : 'Hari 8–14 = 20%' }})
                            @endif
                        </div>
                    </td>
                    <td class="text-end text-danger fw-bold fs-6">
                        Rp {{ number_format($fineAmount, 0, ',', '.') }}
                    </td>
                </tr>
            </tbody>
            <tfoot>
                <tr class="table-light">
                    <th colspan="2" class="text-uppercase text-end fs-6">Total Tagihan Denda:</th>
                    <th class="text-end text-danger fs-5 fw-bold">Rp {{ number_format($fineAmount, 0, ',', '.') }}</th>
                </tr>
            </tfoot>
        </table>

        <!-- Keterangan Kebijakan -->
        <div class="p-3 bg-light rounded small text-secondary mt-3">
            <i class="bi bi-info-circle me-1"></i> <strong>Catatan:</strong>
            Pembayaran denda dilakukan secara manual dan tunai langsung kepada <strong>Petugas Perpustakaan</strong> di meja sirkulasi.
        </div>

        <!-- Tanda Tangan -->
        <div class="row mt-5 pt-2 border-dashed">
            <div class="col-6 text-center">
                <small class="text-muted d-block mb-4">Peminjam / Anggota,</small>
                <div class="fw-bold text-decoration-underline">{{ $loan->user->name }}</div>
                <small class="text-muted">{{ strtoupper($loan->user->id_card_type ?? 'KTM') }}: {{ $loan->user->id_card_number ?? '-' }}</small>
            </div>
            <div class="col-6 text-center">
                <small class="text-muted d-block mb-4">Petugas Sirkulasi,</small>
                <div class="fw-bold text-decoration-underline">{{ auth()->user()->name }}</div>
                <small class="text-muted">PerpusApp Administrator</small>
            </div>
        </div>
    </div>

</body>
</html>