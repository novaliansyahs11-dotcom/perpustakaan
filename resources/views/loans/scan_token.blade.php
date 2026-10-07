@extends('layouts.app')

@section('title', 'Scan Tiket Peminjaman Mandiri')

@section('content')
<div class="d-flex justify-content-between align-items-center pb-2 mb-4 border-bottom">
    <div>
        <h1 class="h3 fw-bold mb-0">Scan Tiket Pinjam Mandiri</h1>
        <small class="text-muted">Arahkan kamera ke QR Code mahasiswa atau gunakan alat scanner barcode USB.</small>
    </div>
    <a href="{{ route('loans.index') }}" class="btn btn-outline-secondary">
        <i class="bi bi-arrow-left me-1"></i> Kembali ke Sirkulasi
    </a>
</div>

{{-- Alert Error / Warning --}}
@if(session('error'))
    <div class="alert alert-danger alert-dismissible fade show" role="alert">
        <i class="bi bi-exclamation-triangle-fill me-2"></i> {{ session('error') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

@if(session('warning'))
    <div class="alert alert-warning alert-dismissible fade show" role="alert">
        <i class="bi bi-info-circle-fill me-2"></i> {{ session('warning') }}
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
@endif

<div class="row g-4">
    {{-- Kolom Kiri: Kamera Scanner QR Code --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 fw-semibold text-primary">
                <i class="bi bi-camera me-1"></i> Scan dengan Kamera Web / Laptop
            </div>
            <div class="card-body text-center p-3">
                <div id="reader" style="width: 100%; min-height: 280px;" class="rounded-3 border bg-light"></div>
                <small class="text-muted d-block mt-2">
                    Izinkan akses kamera browser. Saat QR Code terbaca, sistem langsung memproses secara otomatis.
                </small>
            </div>
        </div>
    </div>

    {{-- Kolom Kanan: Input Alat Scanner USB / Manual --}}
    <div class="col-md-6">
        <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white py-3 fw-semibold text-dark">
                <i class="bi bi-upc-scan me-1"></i> Barcode Scanner USB / Input Manual
            </div>
            <div class="card-body p-4 d-flex flex-column justify-content-center">
                <form id="token-form" action="{{ route('loans.scan-token') }}" method="POST">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label fw-bold">Kode Tiket Token</label>
                        <div class="input-group input-group-lg">
                            <span class="input-group-text bg-light"><i class="bi bi-qr-code"></i></span>
                            <input type="text" 
                                   name="token_code" 
                                   id="token_input" 
                                   class="form-control font-monospace text-uppercase fw-bold" 
                                   placeholder="Contoh: TKN-GXW7MBMI" 
                                   autofocus 
                                   required>
                        </div>
                        <small class="text-muted mt-1 d-block">
                            *Jika menggunakan alat scanner tembak USB, cukup klik kolom ini lalu tembak QR di HP mahasiswa.
                        </small>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg w-100 py-2 fw-semibold">
                        <i class="bi bi-check2-circle me-1"></i> Verifikasi & Serahkan Buku
                    </button>
                </form>
            </div>
        </div>
    </div>
</div>

{{-- Library Scanner Kamera Gratis (html5-qrcode) --}}
<script src="https://unpkg.com/html5-qrcode"></script>
<script>
    function onScanSuccess(decodedText, decodedResult) {
        // Hentikan scanner sementara agar tidak dobel submit
        html5QrcodeScanner.clear();
        
        // Isi input dan submit form otomatis
        document.getElementById('token_input').value = decodedText;
        document.getElementById('token-form').submit();
    }

    let html5QrcodeScanner = new Html5QrcodeScanner(
        "reader", 
        { fps: 10, qrbox: { width: 250, height: 250 } },
        /* verbose= */ false
    );
    html5QrcodeScanner.render(onScanSuccess);
</script>
@endsection