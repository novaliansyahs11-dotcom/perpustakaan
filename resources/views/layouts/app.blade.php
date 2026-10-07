<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Sistem Informasi Perpustakaan')</title>
    <!-- Bootstrap 5 CSS CDN -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    <style>
        body { background-color: #f8f9fa; }
        .sidebar { min-height: 100vh; background-color: #212529; }
        .sidebar .nav-link { color: #adb5bd; border-radius: 6px; margin-bottom: 4px; transition: all 0.2s ease; }
        .sidebar .nav-link:hover, .sidebar .nav-link.active { color: #fff; background-color: #0d6efd; }
    </style>
</head>
<body>
    @auth
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <div class="col-md-3 col-lg-2 d-md-block sidebar collapse p-3 text-white d-flex flex-column">
                <a href="{{ route('dashboard') }}" class="d-flex align-items-center mb-3 mb-md-0 me-md-auto text-white text-decoration-none">
                    <i class="bi bi-book-half fs-4 me-2 text-primary"></i>
                    <span class="fs-5 fw-bold">PerpusApp</span>
                </a>
                <hr>
                <ul class="nav nav-pills flex-column mb-auto">
                    <li class="nav-item">
                        <a href="{{ route('dashboard') }}" class="nav-link {{ request()->routeIs('dashboard') ? 'active' : '' }}">
                            <i class="bi bi-speedometer2 me-2"></i> Dashboard
                        </a>
                    </li>

                    @if(Auth::user()->role === 'admin')
                    <li class="nav-item mt-2 text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 1px;">Katalog</li>
                    <li>
                        <a href="{{ route('categories.index') }}" class="nav-link {{ request()->routeIs('categories.*') ? 'active' : '' }}">
                            <i class="bi bi-collection me-2"></i> Kategori Buku
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('books.index') }}" class="nav-link {{ request()->routeIs('books.*') ? 'active' : '' }}">
                            <i class="bi bi-journals me-2"></i> Data Buku
                        </a>
                    </li>
                    <li class="nav-item mt-2 text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 1px;">Sirkulasi</li>
                    <li>
                        <a href="{{ route('loans.index') }}" class="nav-link {{ request()->routeIs('loans.*') ? 'active' : '' }}">
                            <i class="bi bi-arrow-left-right me-2"></i> Peminjaman
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('members.index') }}" class="nav-link {{ request()->routeIs('members.*') ? 'active' : '' }}">
                            <i class="bi bi-people me-2"></i> Anggota
                        </a>
                    </li>
                    @else
                    <li class="nav-item mt-2 text-uppercase text-secondary" style="font-size: 0.75rem; letter-spacing: 1px;">Member Area</li>
                    <li>
                        <a href="{{ route('member.browse') }}" class="nav-link {{ request()->routeIs('member.browse') ? 'active' : '' }}">
                            <i class="bi bi-search me-2"></i> Cari Buku
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('member.loans') }}" class="nav-link {{ request()->routeIs('member.loans') ? 'active' : '' }}">
                            <i class="bi bi-clock-history me-2"></i> Pinjaman Saya
                        </a>
                    </li>
                    <li>
                        <a href="{{ route('member.profile') }}" class="nav-link {{ request()->routeIs('member.profile*') ? 'active' : '' }}">
                            <i class="bi bi-person-badge me-2"></i> Profil Saya
                        </a>
                    </li>
                    @endif
                </ul>
                <hr>

                <!-- Footer Profil User & Logout -->
                <div class="d-flex align-items-center justify-content-between pt-1">
                    <a href="{{ route('member.profile') }}" class="text-decoration-none text-white d-flex align-items-center gap-2" title="Kelola Profil & Pengaturan">
                        <div class="rounded-circle bg-primary text-white d-flex align-items-center justify-content-center fw-bold" style="width: 34px; height: 34px; font-size: 13px;">
                            {{ strtoupper(substr(Auth::user()->name, 0, 2)) }}
                        </div>
                        <div style="line-height: 1.2;">
                            <strong class="d-block text-truncate" style="max-width: 105px;">{{ Auth::user()->name }}</strong>
                            <small class="text-secondary text-capitalize" style="font-size: 11px;">{{ Auth::user()->role }}</small>
                        </div>
                    </a>
                    <form action="{{ route('logout') }}" method="POST" class="m-0">
                        @csrf
                        <button type="submit" class="btn btn-sm btn-outline-danger p-1 lh-1" title="Logout">
                            <i class="bi bi-box-arrow-right fs-6"></i>
                        </button>
                    </form>
                </div>
            </div>

            <!-- Content Area -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
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

                @yield('content')
            </main>
        </div>
    </div>
    @else
        <main>
            @yield('content')
        </main>
    @endauth

    <!-- Bootstrap 5 JS Bundle CDN -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>