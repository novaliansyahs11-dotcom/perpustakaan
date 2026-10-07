<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BulkBookSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Pastikan direktori penyimpanan cover tersedia
        if (!Storage::disk('public')->exists('covers')) {
            Storage::disk('public')->makeDirectory('covers');
        }

        // 2. Siapkan Kategori Buku
        $catIT = Category::firstOrCreate(['slug' => 'teknologi-informasi'], ['name' => 'Teknologi Informasi']);
        $catBisnis = Category::firstOrCreate(['slug' => 'bisnis-dan-manajemen'], ['name' => 'Bisnis & Manajemen']);
        $catSains = Category::firstOrCreate(['slug' => 'sains-dan-matematika'], ['name' => 'Sains & Matematika']);
        $catDesain = Category::firstOrCreate(['slug' => 'desain-dan-multimedia'], ['name' => 'Desain & Multimedia']);
        $catSastra = Category::firstOrCreate(['slug' => 'sastra-dan-fiksi'], ['name' => 'Sastra & Fiksi']);
        $catSelf = Category::firstOrCreate(['slug' => 'pengembangan-diri'], ['name' => 'Pengembangan Diri']);

        // 3. Daftar 36 Koleksi Master Buku Lengkap dengan Pemetaan File SVG yang Pas
        $booksData = [
            // --- TEKNOLOGI INFORMASI (1-10) ---
            [
                'category_id' => $catIT->id,
                'isbn' => '978-602-00-1001-1',
                'title' => 'Clean Code: A Handbook of Agile Software Craftsmanship',
                'author' => 'Robert C. Martin',
                'publisher' => 'Prentice Hall',
                'publication_year' => 2023,
                'price' => 195000,
                'description' => 'Panduan standar industri menulis kode rapi, mudah dirawat, dan teruji secara profesional.',
                'svg_file' => 'book_4_clean-code-a-h.svg',
                'copies' => 4,
            ],
            [
                'category_id' => $catIT->id,
                'isbn' => '978-602-00-1002-2',
                'title' => 'Mastering Laravel 11: Arsitektur Web Enterprise Modern',
                'author' => 'Taylor Otwell',
                'publisher' => 'Packt Publishing',
                'publication_year' => 2025,
                'price' => 175000,
                'description' => 'Membangun aplikasi backend berkinerja tinggi dengan ekosistem routing, queue, dan Eloquent ORM.',
                'svg_file' => 'book_5_mastering-larav.svg',
                'copies' => 3,
            ],
            [
                'category_id' => $catIT->id,
                'isbn' => '978-602-00-1003-3',
                'title' => 'Pemrograman Flutter & Dart: Dari Dasar hingga Rilis Toko Aplikasi',
                'author' => 'Aditya Ramadhanu',
                'publisher' => 'Informatika Press',
                'publication_year' => 2026,
                'price' => 155000,
                'description' => 'Langkah praktis membuat aplikasi multiplatform Android dan iOS responsif menggunakan framework Flutter.',
                'svg_file' => 'book_6_pemrograman-flu.svg',
                'copies' => 4,
            ],
            [
                'category_id' => $catIT->id,
                'isbn' => '978-602-00-1004-4',
                'title' => 'Arsitektur Basis Data MySQL: Desain Relasional & Indexing',
                'author' => 'Novaliansyah Saputra',
                'publisher' => 'Pustaka Sains IT',
                'publication_year' => 2024,
                'price' => 140000,
                'description' => 'Teknik normalisasi tabel tingkat lanjut, optimasi kueri kompleks, dan integritas referensial foreign key.',
                'svg_file' => 'book_7_arsitektur-basi.svg',
                'copies' => 3,
            ],
            [
                'category_id' => $catIT->id,
                'isbn' => '978-602-00-1005-5',
                'title' => 'Cloud Computing & Arsitektur Microservices',
                'author' => 'Martin Fowler',
                'publisher' => 'Addison-Wesley',
                'publication_year' => 2024,
                'price' => 210000,
                'description' => 'Membagi sistem monolitik menjadi layanan modular terdistribusi berbasis kontainer Docker dan Kubernetes.',
                'svg_file' => 'book_8_cloud-computing.svg',
                'copies' => 2,
            ],
            [
                'category_id' => $catIT->id,
                'isbn' => '978-602-00-1006-6',
                'title' => 'Jaringan Komputer Lanjut: Protokol, Routing, & Hardware Switching',
                'author' => 'Andrew S. Tanenbaum',
                'publisher' => 'Pearson Education',
                'publication_year' => 2023,
                'price' => 185000,
                'description' => 'Memahami topologi jaringan, transmisi data TCP/IP, keamanan router, dan tethering perangkat keras.',
                'svg_file' => 'book_9_jaringan-komput.svg',
                'copies' => 3,
            ],
            [
                'category_id' => $catIT->id,
                'isbn' => '978-602-00-1007-7',
                'title' => 'Keamanan Siber & Ethical Hacking Praktis',
                'author' => 'Georgia Weidman',
                'publisher' => 'No Starch Press',
                'publication_year' => 2024,
                'price' => 190000,
                'description' => 'Teknik penetrasi sistem, audit celah keamanan web, dan proteksi dari serangan SQL Injection.',
                'svg_file' => 'book_10_keamanan-siber.svg',
                'copies' => 2,
            ],
            [
                'category_id' => $catIT->id,
                'isbn' => '978-602-00-1008-8',
                'title' => 'Fundamental Java untuk Pemrograman Berorientasi Objek (OOP)',
                'author' => 'Herbert Schildt',
                'publisher' => 'McGraw-Hill',
                'publication_year' => 2023,
                'price' => 165000,
                'description' => 'Konsep pewarisan (inheritance), enkapsulasi, polimorfisme, dan interface dalam bahasa Java modern.',
                'svg_file' => 'book_11_fundamental-jav.svg',
                'copies' => 4,
            ],
            [
                'category_id' => $catIT->id,
                'isbn' => '978-602-00-1009-9',
                'title' => 'Artificial Intelligence & Machine Learning dengan Python',
                'author' => 'Stuart Russell & Peter Norvig',
                'publisher' => 'Prentice Hall',
                'publication_year' => 2025,
                'price' => 230000,
                'description' => 'Konsep jaringan saraf tiruan (neural networks), computer vision, dan algoritma prediktif berbasis data.',
                'svg_file' => 'book_12_artificial-inte.svg',
                'copies' => 3,
            ],
            [
                'category_id' => $catIT->id,
                'isbn' => '978-602-00-1010-0',
                'title' => 'DevOps Handbook: CI/CD Pipeline & Otomasi Deployment',
                'author' => 'Gene Kim & Jez Humble',
                'publisher' => 'IT Revolution Press',
                'publication_year' => 2024,
                'price' => 170000,
                'description' => 'Membangun pipeline integrasi berkelanjutan menggunakan GitHub Actions dan server staging otomatis.',
                'svg_file' => 'book_13_devops-handbook.svg',
                'copies' => 2,
            ],

            // --- DESAIN & MULTIMEDIA (11-16) ---
            [
                'category_id' => $catDesain->id,
                'isbn' => '978-602-00-2001-1',
                'title' => 'UI/UX Design Masterclass: Wireframing & Prototyping Figma',
                'author' => 'Ken Keisha',
                'publisher' => 'Kreatif Media',
                'publication_year' => 2025,
                'price' => 135000,
                'description' => 'Prinsip desain antarmuka responsif, micro-interaction, dan pembuatan sistem desain (Design System).',
                'svg_file' => 'book_14_uiux-design-ma.svg',
                'copies' => 4,
            ],
            [
                'category_id' => $catDesain->id,
                'isbn' => '978-602-00-2002-2',
                'title' => 'Seni Tipografi Digital & Tata Letak Visual',
                'author' => 'Ellen Lupton',
                'publisher' => 'Princeton Architectural Press',
                'publication_year' => 2023,
                'price' => 125000,
                'description' => 'Mengeksplorasi hirarki visual, pemilihan font berkarakter, dan komposisi layout editorial majalah.',
                'svg_file' => 'book_15_seni-tipografi.svg',
                'copies' => 3,
            ],
            [
                'category_id' => $catDesain->id,
                'isbn' => '978-602-00-2003-3',
                'title' => 'Desain Identitas Merek & Pembuatan Logo Vektor',
                'author' => 'David Airey',
                'publisher' => 'New Riders',
                'publication_year' => 2024,
                'price' => 145000,
                'description' => 'Proses kreatif pembuatan logo ikonik dari sketsa manual hingga digitalisasi menggunakan Adobe Illustrator.',
                'svg_file' => 'book_16_desain-identita.svg',
                'copies' => 3,
            ],
            [
                'category_id' => $catDesain->id,
                'isbn' => '978-602-00-2004-4',
                'title' => 'Dark Theme & Modern Aesthetics in Mobile App Design',
                'author' => 'Creative Pulse',
                'publisher' => 'Digital Arts',
                'publication_year' => 2025,
                'price' => 150000,
                'description' => 'Panduan mendesain tampilan mode gelap elegan dengan kontras warna terkalibrasi dan minim kelelahan mata.',
                'svg_file' => 'book_17_dark-theme-mo.svg',
                'copies' => 2,
            ],
            [
                'category_id' => $catDesain->id,
                'isbn' => '978-602-00-2005-5',
                'title' => 'Dasar Fotografi Komersial Produk Gadget & Mode',
                'author' => 'Scott Kelby',
                'publisher' => 'Rocky Nook',
                'publication_year' => 2024,
                'price' => 160000,
                'description' => 'Teknik pencahayaan studio, komposisi sudut pengambilan gambar (angle), dan pengeditan pascaproduksi.',
                'svg_file' => 'book_18_dasar-fotografi.svg',
                'copies' => 3,
            ],
            [
                'category_id' => $catDesain->id,
                'isbn' => '978-602-00-2006-6',
                'title' => 'Motion Graphics & Animasi Antarmuka Interaktif',
                'author' => 'Angie Taylor',
                'publisher' => 'Focal Press',
                'publication_year' => 2023,
                'price' => 155000,
                'description' => 'Membuat efek transisi dinamis dan animasi grafis vektor menggunakan teknik keyframing modern.',
                'svg_file' => 'book_19_motion-graphics.svg',
                'copies' => 2,
            ],

            // --- BISNIS & MANAJEMEN (17-22) ---
            [
                'category_id' => $catBisnis->id,
                'isbn' => '978-602-00-3001-1',
                'title' => 'Strategi Retail Gadget & Bisnis Smartphone Terintegrasi',
                'author' => 'Aditya R.',
                'publisher' => 'Ekonomi Nusantara',
                'publication_year' => 2025,
                'price' => 120000,
                'description' => 'Pengelolaan inventaris, analisis pasar second-hand elektronik, dan teknik pemasaran media sosial.',
                'svg_file' => 'book_20_strategi-retail.svg',
                'copies' => 4,
            ],
            [
                'category_id' => $catBisnis->id,
                'isbn' => '978-602-00-3002-2',
                'title' => 'The Lean Startup: Inovasi Cepat untuk Bisnis Berkelanjutan',
                'author' => 'Eric Ries',
                'publisher' => 'Crown Business',
                'publication_year' => 2023,
                'price' => 135000,
                'description' => 'Menerapkan siklus Build-Measure-Learn untuk memvalidasi ide produk tanpa membuang banyak modal awal.',
                'svg_file' => 'book_21_the-lean-startu.svg',
                'copies' => 3,
            ],
            [
                'category_id' => $catBisnis->id,
                'isbn' => '978-602-00-3003-3',
                'title' => 'Manajemen Proyek Sistem Informasi (Agile & Scrum)',
                'author' => 'Jeff Sutherland',
                'publisher' => 'Bentang Pustaka',
                'publication_year' => 2024,
                'price' => 140000,
                'description' => 'Memimpin sprint tim pengembang perangkat lunak dengan metode Scrum untuk mencapai target delivery tepat waktu.',
                'svg_file' => 'book_22_manajemen-proye.svg',
                'copies' => 3,
            ],
            [
                'category_id' => $catBisnis->id,
                'isbn' => '978-602-00-3004-4',
                'title' => 'Branding Streetwear & Filosofi Busana Urban',
                'author' => 'Venustas Studio',
                'publisher' => 'Pustaka Busana Kita',
                'publication_year' => 2025,
                'price' => 165000,
                'description' => 'Membangun loyalitas merek apparel independen lewat narasi budaya pop, kualitas kain, dan filosofi desain.',
                'svg_file' => 'book_23_branding-street.svg',
                'copies' => 2,
            ],
            [
                'category_id' => $catBisnis->id,
                'isbn' => '978-602-00-3005-5',
                'title' => 'Pemasaran Digital: SEO, Content Strategy, & Iklan Berbayar',
                'author' => 'Philip Kotler',
                'publisher' => 'Gramedia Utama',
                'publication_year' => 2024,
                'price' => 145000,
                'description' => 'Strategi konversi pelanggan online melalui optimasi kata kunci pencarian dan funneling kampanye iklan.',
                'svg_file' => 'book_24_pemasaran-digit.svg',
                'copies' => 3,
            ],
            [
                'category_id' => $catBisnis->id,
                'isbn' => '978-602-00-3006-6',
                'title' => 'Akuntansi Keuangan & Manajemen Modal UMKM',
                'author' => 'Kasmir',
                'publisher' => 'Rajawali Pers',
                'publication_year' => 2023,
                'price' => 110000,
                'description' => 'Menyusun laporan neraca, arus kas, dan mengukur titik impas (Break Even Point) usaha kecil.',
                'svg_file' => 'book_25_akuntansi-keuan.svg',
                'copies' => 3,
            ],

            // --- SAINS & MATEMATIKA (23-28) ---
            [
                'category_id' => $catSains->id,
                'isbn' => '978-602-00-4001-1',
                'title' => 'Aljabar Linier Terapan: Matriks, Vektor, & Ruang Dimensi',
                'author' => 'Gilbert Strang',
                'publisher' => 'Wellesley-Cambridge',
                'publication_year' => 2023,
                'price' => 150000,
                'description' => 'Konsep eliminasi Gauss-Jordan, determinan matriks, dan transformasi linier untuk komputasi.',
                'svg_file' => 'book_2_aljabar-linier.svg', // <-- Langsung tepat ke file SVG Aljabar Linier
                'copies' => 3,
            ],
            [
                'category_id' => $catSains->id,
                'isbn' => '978-602-00-4002-2',
                'title' => 'Statistika Probabilitas & Inferensial Terapan',
                'author' => 'Ronald E. Walpole',
                'publisher' => 'Penerbit ITB',
                'publication_year' => 2024,
                'price' => 135000,
                'description' => 'Distribusi binomial, regresi linier berganda, dan pengujian hipotesis statistik pada data riil.',
                'svg_file' => 'book_26_statistika-prob.svg',
                'copies' => 4,
            ],
            [
                'category_id' => $catSains->id,
                'isbn' => '978-602-00-4003-3',
                'title' => 'Kalkulus Multivariabel untuk Sains dan Rekayasa',
                'author' => 'James Stewart',
                'publisher' => 'Cengage Learning',
                'publication_year' => 2023,
                'price' => 180000,
                'description' => 'Diferensial parsial, integral lipat dua/tiga, dan penerapan vektor dalam kalkulus ruang.',
                'svg_file' => 'book_27_kalkulus-multiv.svg',
                'copies' => 3,
            ],
            [
                'category_id' => $catSains->id,
                'isbn' => '978-602-00-4004-4',
                'title' => 'Fisika Dasar Mekanika & Termodinamika',
                'author' => 'Halliday & Resnick',
                'publisher' => 'Erlangga',
                'publication_year' => 2022,
                'price' => 170000,
                'description' => 'Hukum gerak Newton, konservasi energi mekanik, kesetimbangan statis, dan hukum termodinamika.',
                'svg_file' => 'book_28_fisika-dasar-me.svg',
                'copies' => 2,
            ],
            [
                'category_id' => $catSains->id,
                'isbn' => '978-602-00-4005-5',
                'title' => 'Metode Komputasi Numerik untuk Pemodelan Ilmiah',
                'author' => 'Steven C. Chapra',
                'publisher' => 'McGraw-Hill',
                'publication_year' => 2024,
                'price' => 160000,
                'description' => 'Aproksimasi numerik, pencarian akar persamaan non-linier, dan interpolasi menggunakan algoritma komputasi.',
                'svg_file' => 'book_29_metode-komputa.svg',
                'copies' => 2,
            ],
            [
                'category_id' => $catSains->id,
                'isbn' => '978-602-00-4006-6',
                'title' => 'Matematika Diskrit: Teori Graf & Logika Proposisi',
                'author' => 'Kenneth H. Rosen',
                'publisher' => 'McGraw-Hill',
                'publication_year' => 2023,
                'price' => 140000,
                'description' => 'Dasar logika matematika untuk struktur data, relasi rekurensi, pohon (tree), dan pewarnaan graf.',
                'svg_file' => 'book_30_matematika-dis.svg',
                'copies' => 3,
            ],

            // --- SASTRA & FIKSI (29-32) ---
            [
                'category_id' => $catSastra->id,
                'isbn' => '978-602-00-5001-1',
                'title' => 'Bumi Manusia',
                'author' => 'Pramoedya Ananta Toer',
                'publisher' => 'Hasta Mitra',
                'publication_year' => 2021,
                'price' => 115000,
                'description' => 'Kisah epik Minke menghadapi pergulatan feodalisme dan kolonialisme di pergantian abad ke-20.',
                'svg_file' => 'book_31_bumi-manusia.svg',
                'copies' => 4,
            ],
            [
                'category_id' => $catSastra->id,
                'isbn' => '978-602-00-5002-2',
                'title' => 'Laskar Pelangi',
                'author' => 'Andrea Hirata',
                'publisher' => 'Bentang Pustaka',
                'publication_year' => 2022,
                'price' => 95000,
                'description' => 'Kisah inspiratif persahabatan sepuluh anak Belitung dalam memperjuangkan mimpi menempuh pendidikan.',
                'svg_file' => 'book_32_laskar-pelangi.svg',
                'copies' => 5,
            ],
            [
                'category_id' => $catSastra->id,
                'isbn' => '978-602-00-5003-3',
                'title' => 'Hujan: Sebuah Kisah Romansa Masa Depan',
                'author' => 'Tere Liye',
                'publisher' => 'Gramedia Pustaka Utama',
                'publication_year' => 2023,
                'price' => 105000,
                'description' => 'Cerita fiksi ilmiah tentang persahabatan, perpisahan, dan teknologi memori di era futuristik.',
                'svg_file' => 'book_33_hujan-sebuah-ki.svg',
                'copies' => 3,
            ],
            [
                'category_id' => $catSastra->id,
                'isbn' => '978-602-00-5004-4',
                'title' => 'Cantik Itu Luka',
                'author' => 'Eka Kurniawan',
                'publisher' => 'Gramedia Pustaka Utama',
                'publication_year' => 2023,
                'price' => 125000,
                'description' => 'Realisme magis yang memadukan sejarah lokal, mitos pedesaan, dan takdir tragis seorang perempuan.',
                'svg_file' => 'book_34_cantik-itu-luk.svg',
                'copies' => 3,
            ],

            // --- PENGEMBANGAN DIRI (33-36) ---
            [
                'category_id' => $catSelf->id,
                'isbn' => '978-602-00-6001-1',
                'title' => 'Atomic Habits: Perubahan Kecil yang Memberikan Hasil Luar Biasa',
                'author' => 'James Clear',
                'publisher' => 'Gramedia',
                'publication_year' => 2024,
                'price' => 108000,
                'description' => 'Sistem praktis membangun kebiasaan produktif setiap hari melalui perbaikan 1% secara konsisten.',
                'svg_file' => 'book_35_atomic-habits.svg',
                'copies' => 5,
            ],
            [
                'category_id' => $catSelf->id,
                'isbn' => '978-602-00-6002-2',
                'title' => 'Filosofi Teras: Menemukan Kedamaian Mental Lewat Stoisisme',
                'author' => 'Henry Manampiring',
                'publisher' => 'Buku Kompas',
                'publication_year' => 2023,
                'price' => 98000,
                'description' => 'Penerapan ajaran filsafat Stoa kuno untuk mengendalikan emosi negatif dan stres di zaman modern.',
                'svg_file' => 'book_36_filosofi-teras.svg',
                'copies' => 4,
            ],
            [
                'category_id' => $catSelf->id,
                'isbn' => '978-602-00-6003-3',
                'title' => 'Deep Work: Aturan untuk Meraih Kesuksesan Tanpa Gangguan',
                'author' => 'Cal Newport',
                'publisher' => 'Grand Central Publishing',
                'publication_year' => 2023,
                'price' => 120000,
                'description' => 'Kemampuan memusatkan konsentrasi penuh pada pekerjaan kognitif rumit di tengah distraksi digital.',
                'svg_file' => 'book_37_deep-work-atur.svg',
                'copies' => 3,
            ],
            [
                'category_id' => $catSelf->id,
                'isbn' => '978-602-00-6004-4',
                'title' => 'Psychology of Money: Pelajaran Abadi Mengenai Kekayaan dan Ketamakan',
                'author' => 'Morgan Housel',
                'publisher' => 'Penerbit Baca',
                'publication_year' => 2024,
                'price' => 105000,
                'description' => 'Memahami bagaimana perilaku, ego, dan persepsi manusia memengaruhi keputusan finansial hidupnya.',
                'svg_file' => 'book_38_psychology-of.svg',
                'copies' => 4,
            ],
        ];

        // 4. Eksekusi Pemasukan & Pemetaan Gambar Akurat
        $availableFiles = glob(storage_path("app/public/covers/*.svg"));

        foreach ($booksData as $index => $data) {
            $coverImage = null;

            // Prioritas 1: Gunakan nama file SVG yang sudah dipetakan langsung
            if (!empty($data['svg_file']) && file_exists(storage_path("app/public/covers/{$data['svg_file']}"))) {
                $coverImage = 'covers/' . $data['svg_file'];
            } 
            
            // Prioritas 2: Pencocokan pintar berdasarkan kata kunci judul jika nama file persis tidak ada
            if (!$coverImage && !empty($availableFiles)) {
                $titleWords = explode('-', Str::slug($data['title']));
                $bestFile = null;
                $maxScore = 0;

                foreach ($availableFiles as $filePath) {
                    $fname = strtolower(basename($filePath));
                    $score = 0;
                    foreach ($titleWords as $word) {
                        if (strlen($word) > 2 && str_contains($fname, $word)) {
                            $score++;
                        }
                    }
                    if ($score > $maxScore) {
                        $maxScore = $score;
                        $bestFile = basename($filePath);
                    }
                }

                if ($bestFile && $maxScore >= 1) {
                    $coverImage = 'covers/' . $bestFile;
                }
            }

            // Simpan atau Perbarui Master Buku
            $book = Book::updateOrCreate(
                ['isbn' => $data['isbn']],
                [
                    'category_id'      => $data['category_id'],
                    'title'            => $data['title'],
                    'author'           => $data['author'],
                    'publisher'        => $data['publisher'],
                    'publication_year' => $data['publication_year'],
                    'price'            => $data['price'],
                    'description'      => $data['description'],
                    'cover_image'      => $coverImage,
                ]
            );

            // Generate Eksemplar Fisik jika belum ada
            if ($book->copies()->count() === 0) {
                $prefix = strtoupper(substr(preg_replace('/[^A-Za-z0-9]/', '', $book->title), 0, 3));
                for ($i = 1; $i <= $data['copies']; $i++) {
                    BookCopy::create([
                        'book_id'   => $book->id,
                        'copy_code' => sprintf('%s-%03d-%02d', $prefix, $book->id, $i),
                        'status'    => 'available',
                    ]);
                }
            }
        }
    }
}