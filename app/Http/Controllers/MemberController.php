<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class MemberController extends Controller
{
    public function index()
    {
        $members = User::where('role', 'member')
            ->withCount(['loans as active_loans_count' => function ($query) {
                $query->where('status', 'borrowed');
            }])
            ->latest()
            ->paginate(10);

        return view('members.index', compact('members'));
    }

    public function show(User $member)
    {
        // Riwayat Sirkulasi & Report Khusus Member Ini
        $loans = $member->loans()
            ->with(['bookCopy.book', 'admin'])
            ->latest()
            ->paginate(10);

        $totalFines = $member->loans()->sum('fine_amount');
        $activeLoansCount = $member->loans()->where('status', 'borrowed')->count();
        $returnedLoansCount = $member->loans()->where('status', 'returned')->count();

        return view('members.show', compact('member', 'loans', 'totalFines', 'activeLoansCount', 'returnedLoansCount'));
    }

    // Cetak Lembar Rekapitulasi Riwayat Peminjaman & Tanggungan Anggota (Admin)
    public function printReport(User $member)
    {
        $loans = $member->loans()
            ->with(['bookCopy.book', 'admin'])
            ->latest()
            ->get();

        $totalFines = $member->loans()->sum('fine_amount');
        $activeLoansCount = $member->loans()->where('status', 'borrowed')->count();

        return view('members.print_report', compact('member', 'loans', 'totalFines', 'activeLoansCount'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', 'unique:users,email'],
            'phone'          => ['required', 'string', 'max:20'],
            'id_card_type'   => ['required', 'in:ktm,ktp,sim'],
            'id_card_number' => ['required', 'string', 'max:50', 'unique:users,id_card_number'],
            'password'       => ['required', 'string', 'min:6'],
        ], [
            'name.required'           => 'Nama lengkap wajib diisi.',
            'email.required'          => 'Alamat email wajib diisi.',
            'email.unique'            => 'Email sudah terdaftar.',
            'phone.required'          => 'Nomor handphone wajib diisi.',
            'id_card_type.required'   => 'Pilih salah satu jenis kartu identitas.',
            'id_card_number.required' => 'Nomor identitas (KTM/KTP/SIM) wajib diisi.',
            'id_card_number.unique'   => 'Nomor identitas sudah terdaftar di sistem.',
            'password.required'       => 'Kata sandi awal wajib diisi.',
            'password.min'            => 'Kata sandi minimal 6 karakter.',
        ]);

        User::create([
            'name'           => $validated['name'],
            'email'          => $validated['email'],
            'phone'          => $validated['phone'],
            'id_card_type'   => $validated['id_card_type'],
            'id_card_number' => $validated['id_card_number'],
            'password'       => Hash::make($validated['password']),
            'role'           => 'member',
            'is_active'      => true,
        ]);

        return redirect()->route('members.index')->with('success', 'Anggota baru berhasil didaftarkan!');
    }

    public function edit(User $member)
    {
        return view('members.edit', compact('member'));
    }

    public function update(Request $request, User $member)
    {
        $validated = $request->validate([
            'name'           => ['required', 'string', 'max:255'],
            'email'          => ['required', 'email', 'max:255', Rule::unique('users')->ignore($member->id)],
            'phone'          => ['required', 'string', 'max:20'],
            'id_card_type'   => ['required', 'in:ktm,ktp,sim'],
            'id_card_number' => ['required', 'string', 'max:50', Rule::unique('users')->ignore($member->id)],
            'password'       => ['nullable', 'string', 'min:6'],
        ], [
            'name.required'           => 'Nama lengkap wajib diisi.',
            'email.required'          => 'Alamat email wajib diisi.',
            'email.unique'            => 'Email sudah digunakan member lain.',
            'id_card_number.unique'   => 'Nomor identitas sudah terdaftar di sistem.',
            'password.min'            => 'Kata sandi baru minimal 6 karakter.',
        ]);

        $updateData = [
            'name'           => $validated['name'],
            'email'          => $validated['email'],
            'phone'          => $validated['phone'],
            'id_card_type'   => $validated['id_card_type'],
            'id_card_number' => $validated['id_card_number'],
        ];

        if (!empty($validated['password'])) {
            $updateData['password'] = Hash::make($validated['password']);
        }

        $member->update($updateData);

        return redirect()->route('members.show', $member->id)->with('success', 'Data anggota berhasil diperbarui!');
    }

    public function toggleStatus(User $member)
    {
        $member->update([
            'is_active' => !$member->is_active,
        ]);

        $statusText = $member->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Akun anggota {$member->name} berhasil {$statusText}.");
    }

    public function destroy(User $member)
    {
        // Proteksi jika masih ada buku yang sedang dipinjam
        if ($member->loans()->where('status', 'borrowed')->exists()) {
            return back()->with('error', 'Gagal menghapus! Anggota ini masih memiliki buku pinjaman yang belum dikembalikan.');
        }

        $member->delete();
        return redirect()->route('members.index')->with('success', 'Data anggota berhasil dihapus.');
    }
}