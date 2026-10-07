@extends('layouts.app')

@section('title', 'Profil Anggota - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold mb-0">Kartu Anggota & Pengaturan Akun</h1>
        <small class="text-muted">Kelola informasi biodata, identitas resmi, dan keamanan akun Anda.</small>
    </div>
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

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0 ps-3">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4 mb-4">
    <!-- Kartu Anggota Digital & Ringkasan Metrik -->
    <div class="col-md-5">
        <!-- Kartu Identitas Digital Member -->
        <div class="card border-0 shadow text-white p-4" style="background: linear-gradient(135deg, #1e3c72 0%, #2a5298 100%); border-radius: 14px;">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div class="d-flex align-items-center">
                    <i class="bi bi-book-half fs-3 me-2 text-warning"></i>
                    <span class="fw-bold fs-5 tracking-wide">PERPUSAPP CARD</span>
                </div>
                <span class="badge bg-light text-primary fw-bold">MEMBER</span>
            </div>

            <div class="mb-3">
                <small class="text-white-50 text-uppercase d-block" style="font-size: 0.75rem;">Nama Anggota</small>
                <h4 class="fw-bold mb-0 text-truncate">{{ $user->name }}</h4>
                <small class="text-white-50">{{ $user->email }}</small>
            </div>

            <div class="mb-3">
                <small class="text-white-50 text-uppercase d-block" style="font-size: 0.7rem;">Nomor Identitas Resmi</small>
                <span class="badge bg-warning text-dark text-uppercase px-2 py-1 fw-bold">
                    {{ $user->id_card_type ?? 'KTM' }}: {{ $user->id_card_number ?? '-' }}
                </span>
            </div>

            <div class="d-flex justify-content-between align-items-end pt-3 border-top border-white-50">
                <div>
                    <small class="text-white-50 d-block" style="font-size: 0.7rem;">Nomor Telepon</small>
                    <span class="fw-semibold small">{{ $user->phone ?? '-' }}</span>
                </div>
                <div>
                    <small class="text-white-50 d-block" style="font-size: 0.7rem;">Status Akun</small>
                    <span class="badge {{ $user->is_active ? 'bg-success' : 'bg-danger' }}">
                        {{ $user->is_active ? 'Aktif' : 'Nonaktif' }}
                    </span>
                </div>
            </div>
        </div>

        <!-- Ringkasan Statistik Pinjaman & Denda -->
        <div class="card border-0 shadow-sm mt-3 p-3">
            <div class="row text-center g-2">
                <div class="col-4 border-end">
                    <h4 class="fw-bold mb-0 text-primary">{{ $totalBorrowed ?? 0 }}</h4>
                    <small class="text-muted" style="font-size: 11px;">Total Riwayat</small>
                </div>
                <div class="col-4 border-end">
                    @php
                        $activeCount = is_countable($activeLoans ?? null) ? count($activeLoans) : ($activeLoan ? 1 : 0);
                    @endphp
                    <h4 class="fw-bold mb-0 {{ $activeCount > 0 ? 'text-warning' : 'text-success' }}">
                        {{ $activeCount }}
                    </h4>
                    <small class="text-muted" style="font-size: 11px;">Pinjam Aktif</small>
                </div>
                <div class="col-4">
                    <h4 class="fw-bold mb-0 text-danger" style="font-size: 1.1rem;">
                        Rp {{ number_format($totalFines ?? 0, 0, ',', '.') }}
                    </h4>
                    <small class="text-muted" style="font-size: 11px;">Total Denda</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Tab Pengaturan: Data Diri & Ganti Password -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm p-4">
            <ul class="nav nav-pills mb-4" id="pills-tab" role="tablist">
                <li class="nav-item" role="presentation">
                    <button class="nav-link active" id="pills-bio-tab" data-bs-toggle="pill" data-bs-target="#pills-bio" type="button" role="tab">
                        <i class="bi bi-person me-1"></i> Data Diri
                    </button>
                </li>
                <li class="nav-item" role="presentation">
                    <button class="nav-link" id="pills-security-tab" data-bs-toggle="pill" data-bs-target="#pills-security" type="button" role="tab">
                        <i class="bi bi-shield-lock me-1"></i> Keamanan Sandi
                    </button>
                </li>
            </ul>

            <div class="tab-content" id="pills-tabContent">
                <!-- Form Ubah Biodata -->
                <div class="tab-pane fade show active" id="pills-bio" role="tabpanel">
                    <form action="{{ route('member.profile.update') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nama Lengkap</label>
                            <input type="text" name="name" class="form-control" value="{{ old('name', $user->name) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Alamat Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email', $user->email) }}" required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Nomor WhatsApp / HP</label>
                            <input type="text" name="phone" class="form-control" value="{{ old('phone', $user->phone) }}" required>
                        </div>

                        <div class="row g-2 mb-4">
                            <div class="col-md-4">
                                <label class="form-label small fw-semibold">Jenis Identitas</label>
                                <select name="id_card_type" class="form-select" required>
                                    <option value="ktm" {{ old('id_card_type', $user->id_card_type) == 'ktm' ? 'selected' : '' }}>KTM (NIM)</option>
                                    <option value="ktp" {{ old('id_card_type', $user->id_card_type) == 'ktp' ? 'selected' : '' }}>KTP (NIK)</option>
                                    <option value="sim" {{ old('id_card_type', $user->id_card_type) == 'sim' ? 'selected' : '' }}>SIM</option>
                                </select>
                            </div>
                            <div class="col-md-8">
                                <label class="form-label small fw-semibold">Nomor Identitas Fisik</label>
                                <input type="text" name="id_card_number" class="form-control" value="{{ old('id_card_number', $user->id_card_number) }}" required>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-primary px-4 shadow-sm">Simpan Data Diri</button>
                    </form>
                </div>

                <!-- Form Ganti Password -->
                <div class="tab-pane fade" id="pills-security" role="tabpanel">
                    <form action="{{ route('member.profile.password') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kata Sandi Saat Ini</label>
                            <input type="password" name="current_password" class="form-control" required placeholder="Masukkan kata sandi lama">
                        </div>
                        <div class="mb-3">
                            <label class="form-label small fw-semibold">Kata Sandi Baru</label>
                            <input type="password" name="password" class="form-control" required placeholder="Minimal 6 karakter">
                        </div>
                        <div class="mb-4">
                            <label class="form-label small fw-semibold">Konfirmasi Kata Sandi Baru</label>
                            <input type="password" name="password_confirmation" class="form-control" required placeholder="Ulangi kata sandi baru">
                        </div>
                        <button type="submit" class="btn btn-outline-danger px-4">Perbarui Kata Sandi</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection