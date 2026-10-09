@extends('layouts.app')

@section('title', 'Lupa Kata Sandi - Sistem Informasi Perpustakaan')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow-sm border-0" style="max-width: 420px; width: 100%;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-key text-primary fs-1"></i>
                <h4 class="fw-bold mt-2">Lupa Kata Sandi</h4>
                <p class="text-muted small">Masukkan alamat email terdaftar untuk menerima instruksi pemulihan kata sandi.</p>
            </div>

            @if (session('status'))
                <div class="alert alert-success py-2 small mb-3">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('status') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 small mb-3">
                    <i class="bi bi-exclamation-triangle-fill me-1"></i> {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('password.email') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold">Alamat Email Member</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="nama@email.com">
                </div>

                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                    <i class="bi bi-envelope-paper me-1"></i> Kirim Link Reset Kata Sandi
                </button>
            </form>

            <div class="mt-4 pt-3 border-top text-center text-muted small">
                Ingat kata sandi Anda? 
                <a href="{{ route('login') }}" class="text-decoration-none fw-semibold text-primary">Kembali ke Login</a>
            </div>
        </div>
    </div>
</div>
@endsection