<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Member - Sistem Informasi Perpustakaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-slate-50 min-h-screen flex items-center justify-center p-4">
    <div class="max-w-md w-full bg-white rounded-2xl shadow-sm border border-slate-100 p-8 my-8">
        
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-12 h-12 rounded-xl bg-blue-50 text-blue-600 mb-3">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-slate-900">Daftar Member Baru</h1>
            <p class="text-sm text-slate-500 mt-1">Lengkapi data untuk membuat akun anggota perpustakaan</p>
        </div>

        @if ($errors->any())
            <div class="mb-4 p-3 rounded-lg bg-red-50 border border-red-200 text-sm text-red-600">
                <ul class="list-disc list-inside space-y-1">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register') }}" method="POST" class="space-y-4">
            @csrf
            
            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nama Lengkap</label>
                <input type="text" name="name" value="{{ old('name') }}" required 
                    placeholder="Masukkan nama lengkap Anda"
                    class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Alamat Email</label>
                <input type="email" name="email" value="{{ old('email') }}" required 
                    placeholder="nama@email.com"
                    class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Nomor Handphone / WhatsApp</label>
                <div class="relative">
                    <span class="absolute inset-y-0 left-0 pl-3.5 flex items-center pointer-events-none text-slate-400 font-medium text-sm">+</span>
                    <input type="text" 
                        inputmode="numeric" 
                        name="phone" 
                        id="phone"
                        value="{{ old('phone') }}" 
                        required 
                        maxlength="14"
                        oninput="formatPhoneNumber(this)"
                        placeholder="6281234567890 (11-14 digit)"
                        class="w-full pl-8 pr-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                </div>
            </div>

            <!-- Jenis & Nomor Identitas Presisi -->
            <div class="grid grid-cols-5 gap-3">
                <div class="col-span-2">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Jenis Kartu</label>
                    <select name="id_card_type" id="id_card_type" required onchange="updateIdValidation()"
                        class="w-full px-3 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition bg-white">
                        <option value="ktm" {{ old('id_card_type') == 'ktm' ? 'selected' : '' }}>KTM (NIM)</option>
                        <option value="ktp" {{ old('id_card_type') == 'ktp' ? 'selected' : '' }}>KTP (NIK)</option>
                        <option value="sim" {{ old('id_card_type') == 'sim' ? 'selected' : '' }}>SIM</option>
                    </select>
                </div>
                <div class="col-span-3">
                    <label class="block text-sm font-medium text-slate-700 mb-1">Nomor Identitas</label>
                    <input type="text" 
                        inputmode="numeric" 
                        name="id_card_number" 
                        id="id_card_number" 
                        value="{{ old('id_card_number') }}" 
                        required 
                        oninput="this.value = this.value.replace(/[^0-9]/g, '');"
                        class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Kata Sandi</label>
                <input type="password" name="password" required 
                    placeholder="Minimal 6 karakter"
                    class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-700 mb-1">Konfirmasi Kata Sandi</label>
                <input type="password" name="password_confirmation" required 
                    placeholder="Ulangi kata sandi"
                    class="w-full px-3.5 py-2 border border-slate-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-blue-500/20 focus:border-blue-600 transition">
            </div>

            <button type="submit" class="w-full bg-blue-600 hover:bg-blue-700 text-white font-medium py-2.5 rounded-lg shadow-sm transition">
                Daftar Sebagai Member
            </button>

            <p class="text-center text-sm text-slate-600 mt-4">
                Sudah memiliki akun? 
                <a href="{{ route('login') }}" class="font-medium text-blue-600 hover:text-blue-700 hover:underline">
                    Masuk di sini
                </a>
            </p>
        </form>
    </div>

    <!-- Script Format Nomor Telepon & Pembatasan Digit Kartu -->
    <script>
    function formatPhoneNumber(input) {
        let val = input.value.replace(/[^0-9]/g, '');
        if (val.startsWith('08')) {
            val = '628' + val.substring(2);
        } else if (val.startsWith('0')) {
            val = '62' + val.substring(1);
        }
        input.value = val;
    }

    function updateIdValidation() {
        const type = document.getElementById('id_card_type').value;
        const input = document.getElementById('id_card_number');

        if (type === 'ktp') {
            input.placeholder = '16 digit NIK KTP';
            input.maxLength = 16;
        } else if (type === 'sim') {
            input.placeholder = '12 digit No. SIM';
            input.maxLength = 12;
        } else {
            input.placeholder = '10-14 digit NIM KTM';
            input.maxLength = 14;
        }
    }

    document.addEventListener("DOMContentLoaded", function() {
        updateIdValidation();
    });
    </script>
</body>
</html>