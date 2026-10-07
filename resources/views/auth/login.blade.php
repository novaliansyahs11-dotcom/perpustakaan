@extends('layouts.app')

@section('title', 'Login - Sistem Informasi Perpustakaan')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow-sm border-0" style="max-width: 400px; width: 100%;">
        <div class="card-body p-4">
            <div class="text-center mb-4">
                <i class="bi bi-book-half text-primary fs-1"></i>
                <h4 class="fw-bold mt-2">Masuk Perpustakaan</h4>
                <p class="text-muted small">Silakan login menggunakan akun terdaftar</p>
            </div>

            @if(session('success'))
                <div class="alert alert-success py-2 small">
                    {{ session('success') }}
                </div>
            @endif

            @if($errors->any())
                <div class="alert alert-danger py-2 small">
                    {{ $errors->first() }}
                </div>
            @endif

            <form action="{{ route('login') }}" method="POST">
                @csrf
                <div class="mb-3">
                    <label for="email" class="form-label small fw-semibold">Alamat Email</label>
                    <input type="email" name="email" id="email" class="form-control" value="{{ old('email') }}" required autofocus placeholder="nama@email.com">
                </div>
                <div class="mb-3">
                    <label for="password" class="form-label small fw-semibold">Kata Sandi</label>
                    <input type="password" name="password" id="password" class="form-control" required placeholder="••••••••">
                </div>
                <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">Masuk ke Sistem</button>
            </form>

            <div class="mt-4 pt-3 border-top text-center text-muted small">
                Belum memiliki akun member? 
                <a href="{{ route('register') }}" class="text-decoration-none fw-semibold text-primary">Daftar Sekarang</a>
            </div>
        </div>
    </div>
</div>
@endsection