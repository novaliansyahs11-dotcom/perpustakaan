<?php

namespace App\Console\Commands;

use App\Models\Book;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class GenerateBookCovers extends Command
{
    protected $signature = 'books:generate-covers';
    protected $description = 'Generate sampul buku SVG otomatis untuk semua buku di database';

    public function handle()
    {
        if (!Storage::disk('public')->exists('covers')) {
            Storage::disk('public')->makeDirectory('covers');
        }

        $palettes = [
            ['#1e3c72', '#2a5298'],
            ['#0f2027', '#203a43'],
            ['#134e5e', '#71b280'],
            ['#2c3e50', '#3498db'],
            ['#4b1248', '#f0c27b'],
            ['#3a1c71', '#d76d77'],
            ['#1f4037', '#99f2c8'],
            ['#232526', '#414345'],
        ];

        $books = Book::all();
        $this->info("Menghasilkan cover untuk {$books->count()} buku...");

        foreach ($books as $index => $book) {
            $colors = $palettes[$index % count($palettes)];
            $filename = 'covers/book_' . $book->id . '_' . Str::slug(substr($book->title, 0, 15)) . '.svg';

            $titleSafe = htmlspecialchars(Str::limit($book->title, 45), ENT_QUOTES, 'UTF-8');
            $authorSafe = htmlspecialchars($book->author, ENT_QUOTES, 'UTF-8');
            $categorySafe = htmlspecialchars($book->category->name ?? 'Perpustakaan', ENT_QUOTES, 'UTF-8');

            $svgContent = <<<SVG
<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 400 580" width="100%" height="100%">
  <defs>
    <linearGradient id="grad{$book->id}" x1="0%" y1="0%" x2="100%" y2="100%">
      <stop offset="0%" style="stop-color:{$colors[0]};stop-opacity:1" />
      <stop offset="100%" style="stop-color:{$colors[1]};stop-opacity:1" />
    </linearGradient>
    <filter id="shadow" x="-5%" y="-5%" width="110%" height="110%">
      <feDropShadow dx="0" dy="4" stdDeviation="6" flood-opacity="0.3"/>
    </filter>
  </defs>
  
  <rect width="400" height="580" rx="8" fill="url(#grad{$book->id})" />
  
  <!-- Aksen garis buku -->
  <line x1="28" y1="0" x2="28" y2="580" stroke="#ffffff" stroke-opacity="0.15" stroke-width="4" />
  <line x1="34" y1="0" x2="34" y2="580" stroke="#000000" stroke-opacity="0.2" stroke-width="2" />

  <!-- Badge Kategori -->
  <rect x="50" y="50" width="160" height="28" rx="14" fill="#ffffff" fill-opacity="0.18" />
  <text x="60" y="68" fill="#ffffff" font-size="12" font-family="system-ui, sans-serif" font-weight="bold" letter-spacing="1">{$categorySafe}</text>

  <!-- Judul Buku -->
  <foreignObject x="50" y="110" width="300" height="260">
    <div xmlns="http://www.w3.org/1999/xhtml" style="color: #ffffff; font-family: system-ui, -apple-system, sans-serif; font-size: 26px; font-weight: 800; line-height: 1.35; text-shadow: 0 2px 4px rgba(0,0,0,0.4);">
      {$titleSafe}
    </div>
  </foreignObject>

  <!-- Ornamen Garis -->
  <line x1="50" y1="440" x2="120" y2="440" stroke="#ffffff" stroke-opacity="0.4" stroke-width="3" />

  <!-- Penulis & PerpusApp -->
  <text x="50" y="475" fill="#ffffff" fill-opacity="0.9" font-size="16" font-family="system-ui, sans-serif" font-weight="600">{$authorSafe}</text>
  <text x="50" y="525" fill="#ffffff" fill-opacity="0.5" font-size="12" font-family="system-ui, sans-serif" letter-spacing="2">PERPUSAPP ARCHIVE</text>
</svg>
SVG;

            Storage::disk('public')->put($filename, $svgContent);
            $book->update(['cover_image' => $filename]);
        }

        $this->info('Semua cover buku berhasil dibuat dan dihubungkan!');
    }
}