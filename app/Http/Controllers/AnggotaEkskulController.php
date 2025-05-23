<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AnggotaEkskul;
use App\Models\User;

class AnggotaEkskulController extends Controller
{
    public function index($ekskulId)
    {
        return response()->json(
            AnggotaEkskul::where('ekskul_id', $ekskulId)->get()
        );
    }

   public function store(Request $request, $ekskulId)
{
    $validated = $request->validate([
        'nama' => 'required|string',
        'kelas' => 'required|string',
        'nisn' => 'nullable|string|max:20',
    ]);

    // 🔒 Cek apakah anggota dengan kombinasi ini sudah ada
    $exists = AnggotaEkskul::where([
        'nama' => $validated['nama'],
        'kelas' => $validated['kelas'],
        'nisn' => $validated['nisn'],
        'ekskul_id' => $ekskulId,
    ])->exists();

    if ($exists) {
        return response()->json([
            'message' => 'Anggota dengan data ini sudah ada!'
        ], 409); // HTTP 409 Conflict
    }

    // ✅ Buat anggota baru jika tidak duplikat
    $anggota = AnggotaEkskul::create([
        'nama' => $validated['nama'],
        'kelas' => $validated['kelas'],
        'nisn' => $validated['nisn'],
        'status' => 'green',
        'ekskul_id' => $ekskulId,
    ]);

    return response()->json($anggota, 201);
}

    public function anggotaTersedia()
{
    $anggota = \App\Models\AnggotaEkskul::select('id', 'nama', 'nisn', 'kelas')->get();

    $kelasList = $anggota->pluck('kelas')->unique()->values();

    return response()->json([
        'kelasList' => $kelasList,
        'namaList' => $anggota
    ]);
}
public function destroy($id)
{
    $anggota = AnggotaEkskul::findOrFail($id);
    $anggota->delete();

    return response()->json(['message' => 'Anggota berhasil dihapus.']);
}
public function siswaTersedia()
{
    $siswa = \App\Models\User::where('role', 'siswa')
        ->select('id', 'nama as name', 'nisn', 'kelas')
        ->get();

    $kelasList = $siswa->pluck('kelas')->unique()->values();

    return response()->json([
        'kelasList' => $kelasList,
        'namaList' => $siswa
    ]);
}


}
