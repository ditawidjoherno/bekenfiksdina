<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AbsensiKaryaWisata;
use App\Models\InfoKaryaWisata;

class AbsensiKaryaWisataController extends Controller
{
  // Controller: AbsensiKaryaWisataController
public function store(Request $request)
{
    $request->validate([
        'kelas' => 'required|string',
        'judul' => 'required|string',
        'data' => 'required|array',
        'data.*.user_id' => 'required|exists:users,id',
        'data.*.status' => 'required|string',
        'data.*.waktu' => 'required|string',
        'data.*.tanggal' => 'required|string',
    ]);


    foreach ($request->data as $absen) {
        AbsensiKaryaWisata::create([
            'user_id' => $absen['user_id'],
            'kelas' => $request->kelas,
            'status' => $absen['status'],
            'waktu' => $absen['waktu'],
            'tanggal' => $absen['tanggal'],
            'judul' => $request->judul,
        ]);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Data absensi berhasil disimpan.'
    ]);
}

public function index(Request $request)
{
    $query = AbsensiKaryaWisata::with('user');

    if ($request->has('kelas')) {
        $query->where('kelas', $request->kelas);
    }

    if ($request->has('judul')) {
        $query->where('judul', $request->judul);
    }

    // ✅ Tambahkan filter tanggal agar tidak error saat query
    if ($request->has('tanggal')) {
        $query->where('tanggal', $request->tanggal);
    }

    $data = $query->get();

    return response()->json(['data' => $data]);
}

}
