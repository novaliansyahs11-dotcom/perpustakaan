@extends('layouts.app')

@section('title', 'Verifikasi Email - PerpusApp')

@section('content')
<div class="container d-flex align-items-center justify-content-center" style="min-height: 100vh;">
    <div class="card shadow-sm border-0" style="max-width: 480px; width: 100%;">
        <div class="card-body p-4 text-center">
            <div class="mb-3">
                <i class="bi bi-envelope-check text-primary" style="font-size: 3.5rem;"></i>
            </div>
            
            <h4 class="fw-bold">Verifikasi Alamat Email Anda</h4>
            <p class="text-muted small mt-2">
                Terima kasih telah mendaftar! Sebelum melanjutkan, silakan periksa kotak masuk (*inbox*) email Anda dan klik link verifikasi yang telah kami kirimkan.
            </p>

            @if (session('success'))
                <div class="alert alert-success py-2 small mb-3">
                    <i class="bi bi-check-circle-fill me-1"></i> {{ session('success') }}
                </div>
            @endif

            <div class="p-3 bg-light rounded text-start mb-4 border">
                <div class="d-flex align-items-center small text-muted">
                    <i class="bi bi-info-circle me-2 text-primary fs-5"></i>
                    <span>
                        <strong>Catatan Pengujian Lokal:</strong> Karena mode mailer diset ke <code>log</code>, link verifikasi dapat Anda temukan di file <code>storage/logs/laravel.log</code>.
                    </span>
                </div>
            </div>

            <div class="d-grid gap-2">
                <form action="{{ route('verification.send') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-primary w-100 py-2 fw-semibold">
                        <i class="bi bi-arrow-clockwise me-1"></i> Kirim Ulang Email Verifikasi
                    </button>
                </form>

                <form action="{{ route('logout') }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-outline-secondary w-100 py-2 fw-semibold">
                        <i class="bi bi-box-arrow-right me-1"></i> Keluar / Logout
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>
@endsection