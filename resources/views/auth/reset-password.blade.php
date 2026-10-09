@extends('layouts.app')

@section('title', 'Atur Ulang Kata Sandi - PerpusApp')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow-sm border-0" style="max-width: 420px; width: 100%;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-shield-lock text-primary fs-1"></i>
                <h4 class="fw-bold mt-2">Atur Ulang Kata Sandi</h4>
                <p class="text-muted small">Buat kata sandi baru untuk akun perpustakaan Anda.</p>
            </div>

            @if($errors->any())
                <div class="alert alert-danger py-2 small mb-3">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('password.update') }}" method="POST">
                @csrf
                <input type="hidden" name="token" value="{{ $token }}">

                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ request('email', old('email')) }}" required autofocus placeholder="nama@email.com">
                </div>

                <div class="mb-3">
                    <label for="password" class="form-label small fw-semibold">Kata Sandi Baru</label>
                    <input type="password" name="password" id="password" class="form-control" required placeholder="Minimal 6 karakter">
                </div>

                <div class="mb-3">
                    <label for="password_confirmation" class="form-label small fw-semibold">Konfirmasi Kata Sandi Baru</label>
                    <input type="password" name="password_confirmation" id="password_confirmation" class="form-control" required placeholder="Ulangi kata sandi baru">
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    Simpan Kata Sandi Baru
                </button>
            </form>
        </div>
    </div>
</div>
@endsection