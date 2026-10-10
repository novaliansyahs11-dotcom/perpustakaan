@extends('layouts.app')

@section('title', 'Data Anggota - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold mb-0">Manajemen Anggota</h1>
        <small class="text-muted">Kelola keanggotaan perpustakaan, identitas resmi, dan histori sirkulasi.</small>
    </div>
    <button type="button" class="btn btn-primary shadow-sm" data-bs-toggle="modal" data-bs-target="#addMemberModal">
        <i class="bi bi-person-plus me-1"></i> Tambah Anggota
    </button>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<!-- Modal Tambah Member -->
<div class="modal fade" id="addMemberModal" tabindex="-1" aria-labelledby="addMemberModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form action="{{ route('members.store') }}" method="POST">
                @csrf
                <div class="modal-header">
                    <h5 class="modal-title fw-bold" id="addMemberModalLabel">Registrasi Anggota Baru</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nama Lengkap</label>
                        <input type="text" name="name" class="form-control" required placeholder="Contoh: Ahmad Fauzi">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Alamat Email</label>
                        <input type="email" name="email" class="form-control" required placeholder="nama@email.com">
                    </div>
                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Nomor Telepon / WhatsApp</label>
                        <input type="text" inputmode="numeric" name="phone" id="modal_phone" class="form-control" required maxlength="14" oninput="formatPhoneNumber(this)" placeholder="628xxxxxxxxxx (Ketik 08... otomatis jadi 628...)">
                    </div>
                    
                    <!-- Pilihan Identitas Fisik (KTM / KTP / SIM) -->
                    <div class="row g-2 mb-3">
                        <div class="col-md-5">
                            <label class="form-label small fw-semibold">Kartu Identitas</label>
                            <select name="id_card_type" id="modal_id_card_type" class="form-select" required onchange="updateIdValidation()">
                                <option value="ktm">KTM (NIM)</option>
                                <option value="ktp">KTP (NIK)</option>
                                <option value="sim">SIM</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label class="form-label small fw-semibold">Nomor Identitas</label>
                            <input type="text" inputmode="numeric" name="id_card_number" id="modal_id_card_number" class="form-control" required oninput="validateIdNumber(this)" placeholder="Masukkan nomor kartu">
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold">Kata Sandi Awal</label>
                        <input type="password" name="password" class="form-control" required minlength="8" placeholder="Min. 8 karakter (Huruf besar, kecil, angka, simbol)">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="submit" class="btn btn-primary">Simpan Anggota</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Tabel Daftar Anggota -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                    <tr>
                        <th class="ps-3" style="width: 50px;">No</th>
                        <th>Nama Anggota</th>
                        <th>Kontak & Identitas</th>
                        <th>Pinjaman Aktif</th>
                        <th>Status Akun</th>
                        <th class="text-center" style="width: 230px;">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($members as $index => $member)
                    <tr>
                        <td class="ps-3">{{ $members->firstItem() + $index }}</td>
                        <td>
                            <strong class="d-block">{{ $member->name }}</strong>
                            <small class="text-muted">Terdaftar: {{ $member->created_at ? $member->created_at->format('d M Y') : '-' }}</small>
                        </td>
                        <td>
                            <div>{{ $member->email }}</div>
                            <small class="text-muted">{{ $member->phone ?? '-' }}</small>
                            @if($member->id_card_number)
                                <div class="mt-1">
                                    <span class="badge bg-light text-dark border text-uppercase" style="font-size: 10px;">
                                        {{ $member->id_card_type }}: {{ $member->id_card_number }}
                                    </span>
                                </div>
                            @endif
                        </td>
                        <td>
                            @php
                                $activeLoans = $member->active_loans_count ?? $member->loans_count ?? 0;
                            @endphp
                            @if($activeLoans > 0)
                                <span class="badge bg-warning text-dark">{{ $activeLoans }} Buku</span>
                            @else
                                <span class="badge bg-light text-muted border">0 Buku</span>
                            @endif
                        </td>
                        <td>
                            @if($member->is_active)
                                <span class="badge bg-success">Aktif</span>
                            @else
                                <span class="badge bg-danger">Nonaktif</span>
                            @endif
                        </td>
                        <td class="text-center pe-3">
                            <div class="btn-group" role="group">
                                <!-- Tombol Rincian & Histori Sirkulasi -->
                                <a href="{{ route('members.show', $member->id) }}" class="btn btn-sm btn-outline-info" title="Lihat Profil & Laporan Sirkulasi">
                                    <i class="bi bi-eye"></i>
                                </a>

                                <!-- Tombol Edit Data Member -->
                                <a href="{{ route('members.edit', $member->id) }}" class="btn btn-sm btn-outline-warning" title="Edit Data Anggota">
                                    <i class="bi bi-pencil"></i>
                                </a>

                                <!-- Tombol Toggle Status Akun -->
                                <form action="{{ route('members.toggle', $member->id) }}" method="POST" class="d-inline">
                                    @csrf
                                    @method('PATCH')
                                    @if($member->is_active)
                                        <button type="submit" class="btn btn-sm btn-outline-secondary" title="Nonaktifkan Akun">
                                            <i class="bi bi-person-x"></i>
                                        </button>
                                    @else
                                        <button type="submit" class="btn btn-sm btn-outline-success" title="Aktifkan Akun">
                                            <i class="bi bi-person-check"></i>
                                        </button>
                                    @endif
                                </form>

                                <!-- Tombol Hapus Anggota -->
                                <form action="{{ route('members.destroy', $member->id) }}" method="POST" class="d-inline" onsubmit="return confirm('Hapus data anggota ini? Tindakan tidak dapat dibatalkan.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-sm btn-outline-danger" title="Hapus Anggota">
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-4 text-muted">Belum ada anggota terdaftar.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    <div class="card-footer bg-white py-3">
        {{ $members->links('pagination::bootstrap-5') }}
    </div>
</div>

<!-- Script Format Nomor Telepon & Pembatasan Digit Identitas -->
<script>
function formatPhoneNumber(input) {
    let val = input.value.replace(/[^0-9]/g, '');
    if (val.startsWith('08')) {
        val = '628' + val.substring(2);
    } else if (val.startsWith('0') && !val.startsWith('62')) {
        val = '62' + val.substring(1);
    }
    input.value = val;
}

function updateIdValidation() {
    const type = document.getElementById('modal_id_card_type').value;
    const input = document.getElementById('modal_id_card_number');

    if (type === 'ktp') {
        input.placeholder = 'Wajib 16 digit NIK';
        input.maxLength = 16;
    } else if (type === 'sim') {
        input.placeholder = 'Wajib 12 digit SIM';
        input.maxLength = 12;
    } else {
        input.placeholder = '10 - 14 digit NIM';
        input.maxLength = 14;
    }
    
    if (input.value.length > input.maxLength) {
        input.value = input.value.substring(0, input.maxLength);
    }
}

function validateIdNumber(input) {
    let val = input.value.replace(/[^0-9]/g, '');
    const max = parseInt(input.maxLength) || 16;
    if (val.length > max) {
        val = val.substring(0, max);
    }
    input.value = val;
}

document.addEventListener("DOMContentLoaded", function() {
    updateIdValidation();
});
</script>
@endsection