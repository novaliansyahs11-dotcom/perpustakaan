@extends('layouts.app')

@section('title', 'Profil Pengguna - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold mb-0">Profil Pengguna</h1>
        <small class="text-muted">Kelola informasi identitas akun dan keamanan kata sandi Anda.</small>
    </div>
</div>

@if(session('success'))
    <div class="alert alert-success alert-dismissible fade show" role="alert">
        {{ session('success') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="row g-4">
    <!-- Form Biodata -->
    <div class="col-md-7">
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-person-badge me-2 text-primary"></i>Informasi Akun</h5>
            <form action="{{ route('profile.update') }}" method="POST">
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

                <div class="row g-2 mb-3">
                    <div class="col-md-4">
                        <label class="form-label small fw-semibold">Jenis Identitas</label>
                        <select name="id_card_type" class="form-select" required>
                            <option value="ktm" {{ old('id_card_type', $user->id_card_type) == 'ktm' ? 'selected' : '' }}>KTM (Mahasiswa)</option>
                            <option value="ktp" {{ old('id_card_type', $user->id_card_type) == 'ktp' ? 'selected' : '' }}>KTP (NIK)</option>
                            <option value="sim" {{ old('id_card_type', $user->id_card_type) == 'sim' ? 'selected' : '' }}>SIM</option>
                        </select>
                    </div>
                    <div class="col-md-8">
                        <label class="form-label small fw-semibold">Nomor Identitas Resmi</label>
                        <input type="text" name="id_card_number" class="form-control" value="{{ old('id_card_number', $user->id_card_number) }}" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </form>
        </div>
    </div>

    <!-- Form Ganti Password -->
    <div class="col-md-5">
        <div class="card border-0 shadow-sm p-4">
            <h5 class="fw-bold mb-3"><i class="bi bi-shield-lock me-2 text-danger"></i>Keamanan Sandi</h5>
            <form action="{{ route('profile.password') }}" method="POST">
                @csrf
                @method('PUT')

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Kata Sandi Saat Ini</label>
                    <input type="password" name="current_password" class="form-control" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Kata Sandi Baru</label>
                    <input type="password" name="password" class="form-control" placeholder="Minimal 6 karakter" required>
                </div>

                <div class="mb-3">
                    <label class="form-label small fw-semibold">Konfirmasi Sandi Baru</label>
                    <input type="password" name="password_confirmation" class="form-control" required>
                </div>

                <button type="submit" class="btn btn-outline-danger">Perbarui Sandi</button>
            </form>
        </div>
    </div>
</div>
@endsection