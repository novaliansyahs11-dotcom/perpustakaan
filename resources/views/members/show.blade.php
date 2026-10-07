@extends('layouts.app')

@section('title', 'Detail Anggota - ' . $member->name)

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold mb-0">Rincian Anggota & Laporan Sirkulasi</h1>
        <small class="text-muted">Profil anggota resmi, kartu identitas, dan riwayat sirkulasi peminjaman.</small>
    </div>
    <div class="d-flex gap-2">
        <!-- Tombol Cetak Laporan Riwayat Member -->
        <a href="{{ route('members.print', $member->id) }}" target="_blank" class="btn btn-outline-primary shadow-sm">
            <i class="bi bi-printer me-1"></i> Cetak Laporan Member
        </a>
        <a href="{{ route('members.edit', $member->id) }}" class="btn btn-warning shadow-sm">
            <i class="bi bi-pencil me-1"></i> Edit Data
        </a>
        <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">
            Kembali
        </a>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4 mb-4">
    <!-- Informasi Profil Member -->
    <div class="col-md-4">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
                <div class="text-center mb-3">
                    <div class="bg-primary text-white rounded-circle d-inline-flex align-items-center justify-content-center shadow-sm" style="width: 64px; height: 64px; font-size: 24px;">
                        {{ strtoupper(substr($member->name, 0, 2)) }}
                    </div>
                    <h5 class="fw-bold mt-2 mb-0">{{ $member->name }}</h5>
                    <span class="badge {{ $member->is_active ? 'bg-success' : 'bg-danger' }} mt-1">
                        {{ $member->is_active ? 'Akun Aktif' : 'Nonaktif' }}
                    </span>
                </div>
                <hr>
                <div class="small">
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Alamat Email:</span>
                        <span class="fw-semibold">{{ $member->email }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">No. WhatsApp:</span>
                        <span class="fw-semibold">{{ $member->phone ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Identitas Fisik:</span>
                        <span class="badge bg-secondary text-uppercase">{{ $member->id_card_type ?? 'KTM' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Nomor Identitas:</span>
                        <span class="fw-semibold">{{ $member->id_card_number ?? '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2 border-bottom">
                        <span class="text-muted">Tanggal Registrasi:</span>
                        <span>{{ $member->created_at ? $member->created_at->format('d M Y') : '-' }}</span>
                    </div>
                    <div class="d-flex justify-content-between py-2">
                        <span class="text-muted">Total Denda Historis:</span>
                        <span class="fw-bold text-danger">Rp {{ number_format($totalFines ?? 0, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Ringkasan Status Tanggungan -->
                <div class="alert {{ ($activeLoansCount ?? 0) > 0 ? 'alert-warning' : 'alert-success' }} mb-0 mt-3 p-2 text-center small">
                    <strong>Status:</strong>
                    {{ ($activeLoansCount ?? 0) > 0 ? ($activeLoansCount . ' Buku Belum Kembali') : 'Bebas Tanggungan Pinjaman' }}
                </div>
            </div>
        </div>
    </div>

    <!-- Tabel Riwayat Sirkulasi Per Member -->
    <div class="col-md-8">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white fw-bold py-3 d-flex justify-content-between align-items-center">
                <span><i class="bi bi-clock-history me-1 text-primary"></i> Riwayat Transaksi & Denda Anggota</span>
                <span class="badge bg-light text-dark border">Total: {{ $loans->total() }} Catatan</span>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-3">Buku & Kode Fisik</th>
                            <th>Tgl Pinjam</th>
                            <th>Jatuh Tempo</th>
                            <th>Tgl Kembali</th>
                            <th>Denda</th>
                            <th class="text-center">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($loans as $loan)
                        @php
                            $today = \Carbon\Carbon::now()->startOfDay();
                            $dueDate = \Carbon\Carbon::parse($loan->due_date)->startOfDay();
                            $isOverdue = $today->gt($dueDate) && $loan->status === 'borrowed';
                        @endphp
                        <tr>
                            <td class="ps-3">
                                <strong>{{ $loan->bookCopy->book->title ?? '-' }}</strong><br>
                                <small class="text-primary font-monospace">{{ $loan->bookCopy->copy_code ?? '-' }}</small>
                            </td>
                            <td>{{ $loan->borrow_date }}</td>
                            <td>
                                <span class="{{ $isOverdue ? 'text-danger fw-bold' : '' }}">
                                    {{ $loan->due_date }}
                                </span>
                                @if($isOverdue)
                                    <small class="text-danger d-block" style="font-size: 10px;">(Terlambat)</small>
                                @endif
                            </td>
                            <td>{{ $loan->return_date ?? '-' }}</td>
                            <td>
                                @if($loan->fine_amount > 0)
                                    <span class="badge bg-danger">Rp {{ number_format($loan->fine_amount, 0, ',', '.') }}</span>
                                @else
                                    <span class="text-muted">-</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($loan->status == 'returned')
                                    <span class="badge bg-success">Dikembalikan</span>
                                @elseif($isOverdue)
                                    <span class="badge bg-danger">Terlambat</span>
                                @else
                                    <span class="badge bg-warning text-dark">Dipinjam</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="6" class="text-center py-4 text-muted">Belum ada catatan sirkulasi untuk anggota ini.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="card-footer bg-white py-3">
                {{ $loans->links('pagination::bootstrap-5') }}
            </div>
        </div>
    </div>
</div>
@endsection