<?php

namespace App\Http\Controllers;

namespace App\Http\Controllers;

use App\Models\Absensi;
use App\Models\User;
use Illuminate\Http\Request;

class AbsensiController extends Controller
{
    // Tampilkan semua data absensi
    public function index()
    {
        $absensi = Absensi::with('user')->latest()->get();
        return response()->json($absensi);
    }

    // Tambah data absensi
    public function store(Request $request)
    {
        $request->validate([
            'kelas' => 'required|string|max:20',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'mulai' => 'required|date_format:H:i',
            'nomor' => 'required|integer',
            'siswa_id' => 'required|exists:users,id',
            'hadir' => 'required|boolean',
            'tidak_hadir' => 'required|boolean',
            'terlambat' => 'required|boolean',
        ]);

        $mulai = $request->input('mulai');
        $selesai = date('H:i:s', strtotime($mulai) + 600); // +10 menit

        $absensi = Absensi::create([
            'kelas' => $request->input('kelas'),
            'hari' => $request->input('hari'),
            'mulai' => $mulai,
            'selesai' => $selesai,
            'nomor' => $request->input('nomor'),
            'siswa_id' => $request->input('siswa_id'),
            'hadir' => $request->input('hadir'),
            'tidak_hadir' => $request->input('tidak_hadir'),
            'terlambat' => $request->input('terlambat'),
        ]);

        return response()->json(['message' => 'Absensi berhasil ditambahkan', 'data' => $absensi], 201);
    }

    // Menampilkan data absensi tertentu
    public function show($id)
    {
        $absensi = Absensi::with('user')->findOrFail($id);
        return response()->json($absensi);
    }

    // Update data absensi
    public function update(Request $request, $id)
    {
        $absensi = Absensi::findOrFail($id);

        $request->validate([
            'kelas' => 'required|string|max:20',
            'hari' => 'required|in:Senin,Selasa,Rabu,Kamis,Jumat,Sabtu,Minggu',
            'mulai' => 'required|date_format:H:i',
            'nomor' => 'required|integer',
            'siswa_id' => 'required|exists:users,id',
            'hadir' => 'required|boolean',
            'tidak_hadir' => 'required|boolean',
            'terlambat' => 'required|boolean',
        ]);

        $mulai = $request->input('mulai');
        $selesai = date('H:i:s', strtotime($mulai) + 600);

        $absensi->update([
            'kelas' => $request->input('kelas'),
            'hari' => $request->input('hari'),
            'mulai' => $mulai,
            'selesai' => $selesai,
            'nomor' => $request->input('nomor'),
            'siswa_id' => $request->input('siswa_id'),
            'hadir' => $request->input('hadir'),
            'tidak_hadir' => $request->input('tidak_hadir'),
            'terlambat' => $request->input('terlambat'),
        ]);

        return response()->json(['message' => 'Absensi berhasil diperbarui', 'data' => $absensi]);
    }

    // Hapus data absensi
    public function destroy($id)
    {
        $absensi = Absensi::findOrFail($id);
        $absensi->delete();

        return response()->json(['message' => 'Absensi berhasil dihapus']);
    }
}
