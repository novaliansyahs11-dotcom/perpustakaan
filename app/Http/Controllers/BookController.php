<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookCopy;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class BookController extends Controller
{
    public function index(Request $request)
    {
        $categories = Category::all();

        $query = Book::with(['category', 'copies']);

        // Filter Pencarian (Judul, Penulis, atau ISBN)
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('author', 'like', "%{$search}%")
                  ->orWhere('isbn', 'like', "%{$search}%");
            });
        }

        // Filter Berdasarkan Kategori
        if ($request->filled('category_id')) {
            $query->where('category_id', $request->category_id);
        }

        $books = $query->latest()->paginate(10)->withQueryString();

        return view('books.index', compact('books', 'categories'));
    }

    public function create()
    {
        $categories = Category::all();
        return view('books.create', compact('categories'));
    }

    public function store(Request $request)
    {
        // Aturan validasi dan pesan kustom bahasa Indonesia
        $rules = [
            'category_id'      => 'required|exists:categories,id',
            'isbn'             => 'required|string|max:50|unique:books,isbn',
            'title'            => 'required|string|max:255',
            'author'           => 'required|string|max:255',
            'publisher'        => 'required|string|max:255',
            'publication_year' => 'required|integer|min:1900|max:' . (date('Y') + 4),
            'price'            => 'required|numeric|min:0',
            'total_copies'     => 'required|integer|min:1|max:50',
            'description'      => 'nullable|string',
            'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ];

        $messages = [
            'category_id.required'      => 'Silakan pilih kategori buku.',
            'category_id.exists'        => 'Kategori buku yang dipilih tidak valid.',
            'isbn.required'             => 'Nomor ISBN wajib diisi.',
            'isbn.unique'               => 'Nomor ISBN ini sudah terdaftar di sistem.',
            'title.required'            => 'Judul buku wajib diisi.',
            'author.required'           => 'Nama penulis wajib diisi.',
            'publisher.required'        => 'Nama penerbit wajib diisi.',
            'publication_year.required' => 'Tahun terbit wajib diisi.',
            'publication_year.integer'  => 'Tahun terbit harus berupa angka bulat.',
            'publication_year.min'      => 'Tahun terbit tidak valid (minimal tahun 1900 dan tidak boleh minus).',
            'publication_year.max'      => 'Tahun terbit tidak boleh melebihi tahun mendatang.',
            'price.required'            => 'Harga buku wajib diisi.',
            'price.numeric'             => 'Harga buku harus berupa format angka.',
            'price.min'                 => 'Harga buku tidak boleh kurang dari 0 atau berupa angka minus!',
            'total_copies.required'     => 'Jumlah eksemplar fisik wajib diisi.',
            'total_copies.integer'      => 'Jumlah eksemplar fisik harus berupa angka bulat.',
            'total_copies.min'          => 'Jumlah eksemplar fisik minimal 1 buku (tidak boleh 0 atau minus)!',
            'total_copies.max'          => 'Jumlah eksemplar fisik maksimal 50 unit per input.',
            'cover_image.image'         => 'File cover harus berupa berkas gambar.',
            'cover_image.mimes'         => 'Format cover buku yang didukung: JPG, JPEG, PNG, WEBP, atau SVG.',
            'cover_image.max'           => 'Ukuran file sampul buku maksimal 2MB.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($request->hasFile('cover_image')) {
            $validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        // Bungkus penyimpanan dalam DB Transaction agar aman
        DB::transaction(function () use ($validated, $request) {
            $book = Book::create($validated);

            $cleanTitle = preg_replace('/[^A-Za-z0-9]/', '', $book->title);
            $prefix = strtoupper(substr($cleanTitle, 0, 3) ?: 'BOK');

            for ($i = 1; $i <= (int) $request->total_copies; $i++) {
                BookCopy::create([
                    'book_id'   => $book->id,
                    'copy_code' => sprintf('%s-%03d-%02d', $prefix, $book->id, $i),
                    'status'    => 'available',
                ]);
            }
        });

        return redirect()->route('books.index')->with('success', 'Buku dan eksemplar fisik berhasil ditambahkan!');
    }

    public function show(Book $book)
    {
        $book->load(['category', 'copies.loans.user']);
        return view('books.show', compact('book'));
    }

    public function edit(Book $book)
    {
        $categories = Category::all();
        return view('books.edit', compact('book', 'categories'));
    }

    public function update(Request $request, Book $book)
    {
        $rules = [
            'category_id'      => 'required|exists:categories,id',
            'isbn'             => 'required|string|max:50|unique:books,isbn,' . $book->id,
            'title'            => 'required|string|max:255',
            'author'           => 'required|string|max:255',
            'publisher'        => 'required|string|max:255',
            'publication_year' => 'required|integer|min:1900|max:' . (date('Y') + 4),
            'price'            => 'required|numeric|min:0',
            'description'      => 'nullable|string',
            'cover_image'      => 'nullable|image|mimes:jpeg,png,jpg,webp,svg|max:2048',
        ];

        $messages = [
            'category_id.required'      => 'Silakan pilih kategori buku.',
            'isbn.required'             => 'Nomor ISBN wajib diisi.',
            'isbn.unique'               => 'Nomor ISBN ini sudah terdaftar pada buku lain.',
            'title.required'            => 'Judul buku wajib diisi.',
            'author.required'           => 'Nama penulis wajib diisi.',
            'publisher.required'        => 'Nama penerbit wajib diisi.',
            'publication_year.min'      => 'Tahun terbit tidak valid dan tidak boleh berupa angka minus.',
            'price.min'                 => 'Harga buku tidak boleh kurang dari 0 atau berupa angka minus!',
            'cover_image.image'         => 'File cover harus berupa berkas gambar.',
            'cover_image.max'           => 'Ukuran file sampul buku maksimal 2MB.',
        ];

        $validated = $request->validate($rules, $messages);

        if ($request->hasFile('cover_image')) {
            if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
                Storage::disk('public')->delete($book->cover_image);
            }
            $validated['cover_image'] = $request->file('cover_image')->store('covers', 'public');
        }

        $book->update($validated);

        return redirect()->route('books.index')->with('success', 'Data buku berhasil diperbarui!');
    }

    public function addCopy(Request $request, Book $book)
    {
        $request->validate([
            'additional_copies' => 'required|integer|min:1|max:20',
        ], [
            'additional_copies.required' => 'Jumlah eksemplar tambahan wajib diisi.',
            'additional_copies.min'      => 'Jumlah eksemplar tambahan minimal 1 dan tidak boleh minus!',
            'additional_copies.max'      => 'Maksimal penambahan adalah 20 unit per proses.',
        ]);

        $lastCopyCount = $book->copies()->count();
        $cleanTitle = preg_replace('/[^A-Za-z0-9]/', '', $book->title);
        $prefix = strtoupper(substr($cleanTitle, 0, 3) ?: 'BOK');

        for ($i = 1; $i <= (int) $request->additional_copies; $i++) {
            $nextNumber = $lastCopyCount + $i;
            BookCopy::create([
                'book_id'   => $book->id,
                'copy_code' => sprintf('%s-%03d-%02d', $prefix, $book->id, $nextNumber),
                'status'    => 'available',
            ]);
        }

        return back()->with('success', "Berhasil menambahkan {$request->additional_copies} eksemplar fisik baru!");
    }

    public function destroy(Book $book)
    {
        $hasActiveLoan = $book->copies()->where('status', 'borrowed')->exists();
        if ($hasActiveLoan) {
            return back()->with('error', 'Buku tidak dapat dihapus karena masih ada unit eksemplar yang sedang dipinjam!');
        }

        if ($book->cover_image && Storage::disk('public')->exists($book->cover_image)) {
            Storage::disk('public')->delete($book->cover_image);
        }

        $book->delete();
        return redirect()->route('books.index')->with('success', 'Buku beserta unit eksemplarnya berhasil dihapus!');
    }
}