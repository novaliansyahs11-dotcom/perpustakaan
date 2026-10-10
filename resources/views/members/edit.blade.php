@extends('layouts.app')

@section('title', 'Edit Anggota - Perpustakaan')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold mb-0">Edit Data Anggota</h1>
        <small class="text-muted">Perbarui informasi keanggotaan dan identitas resmi.</small>
    </div>
    <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali
    </a>
</div>

@if ($errors->any())
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <ul class="mb-0">
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    </div>
@endif

<div class="card border-0 shadow-sm" style="max-width: 700px;">
    <div class="card-body p-4">
        <form action="{{ route('members.update', $member->id) }}" method="POST">
            @csrf
            @method('PUT')

            <div class="mb-3">
                <label class="form-label small fw-semibold">Nama Lengkap</label>
                <input type="text" name="name" class="form-control" value="{{ old('name', $member->name) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Alamat Email</label>
                <input type="email" name="email" class="form-control" value="{{ old('email', $member->email) }}" required>
            </div>

            <div class="mb-3">
                <label class="form-label small fw-semibold">Nomor Telepon / WhatsApp</label>
                <input type="text" inputmode="numeric" name="phone" id="edit_phone" class="form-control" value="{{ old('phone', $member->phone) }}" required maxlength="14" oninput="formatPhoneNumber(this)">
                <small class="text-muted" style="font-size: 11px;">Ketik awalan 08..., sistem otomatis mengubahnya menjadi 628...</small>
            </div>

            <div class="row g-2 mb-3">
                <div class="col-md-5">
                    <label class="form-label small fw-semibold">Kartu Identitas</label>
                    <select name="id_card_type" id="edit_id_card_type" class="form-select" required onchange="updateIdValidation()">
                        <option value="ktm" {{ old('id_card_type', $member->id_card_type) == 'ktm' ? 'selected' : '' }}>KTM (NIM)</option>
                        <option value="ktp" {{ old('id_card_type', $member->id_card_type) == 'ktp' ? 'selected' : '' }}>KTP (NIK)</option>
                        <option value="sim" {{ old('id_card_type', $member->id_card_type) == 'sim' ? 'selected' : '' }}>SIM</option>
                    </select>
                </div>
                <div class="col-md-7">
                    <label class="form-label small fw-semibold">Nomor Identitas</label>
                    <input type="text" inputmode="numeric" name="id_card_number" id="edit_id_card_number" class="form-control" value="{{ old('id_card_number', $member->id_card_number) }}" required oninput="validateIdNumber(this)">
                </div>
            </div>

            <div class="mb-4">
                <label class="form-label small fw-semibold">Kata Sandi Baru (Opsional)</label>
                <input type="password" name="password" class="form-control" minlength="8" placeholder="Kosongkan jika tidak ingin mengubah sandi">
                <small class="text-muted" style="font-size: 11px;">Minimal 8 karakter (huruf besar, kecil, angka, simbol) jika diisi.</small>
            </div>

            <div class="d-flex justify-content-end gap-2">
                <a href="{{ route('members.index') }}" class="btn btn-outline-secondary">Batal</a>
                <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Script Format & Validasi -->
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
    const type = document.getElementById('edit_id_card_type').value;
    const input = document.getElementById('edit_id_card_number');

    if (type === 'ktp') {
        input.maxLength = 16;
    } else if (type === 'sim') {
        input.maxLength = 12;
    } else {
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