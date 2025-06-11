<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InfoKaryaWisata;
use Illuminate\Support\Facades\DB;

class InfoKaryaWisataController extends Controller
{
    // Menyimpan atau memperbarui data info karya wisata (karena hanya 1 entry)
    public function storeOrUpdate(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'tanggal' => 'required|date',
        ]);

        // Simpan atau update entri pertama (asumsi hanya ada 1)
        $info = InfoKaryaWisata::create($validated);


        return response()->json([
            'status' => 'success',
            'data' => $info
        ]);
    }

    // Ambil info karya wisata yang terakhir
    public function show()
{
    $info = \App\Models\InfoKaryaWisata::first(); // ambil data pertama

    if (!$info) {
        return response()->json([
            'status' => 'not_found',
            'data' => null
        ]);
    }

    return response()->json([
        'status' => 'success',
        'data' => $info
    ]);
}
public function list()
{
    $riwayat = \App\Models\InfoKaryaWisata::orderBy('tanggal', 'desc')->get();
    return response()->json($riwayat);
}
public function getCurrentTitle()
{
    $info = \App\Models\InfoKaryaWisata::first();

    if (!$info) {
        return response()->json([
            'status' => 'not_found',
            'data' => null
        ]);
    }

    return response()->json([
        'status' => 'success',
        'data' => [
            'title' => $info->title,
            'tanggal' => $info->tanggal,
        ]
    ]);
}
public function store(Request $request)
{
    $validated = $request->validate([
        'title' => 'required|string|max:255',
        'tanggal' => 'required|date',
    ]);

    $info = InfoKaryaWisata::create($validated);

    return response()->json([
        'status' => 'success',
        'data' => $info
    ]);
}
public function index()
{
    $data = InfoKaryaWisata::orderByDesc('created_at')->first(); // Ambil yang paling baru
    return response()->json(['data' => $data]);
}
public function latest()
{
    $latest = \App\Models\InfoKaryaWisata::orderByDesc('created_at')->first();
    return response()->json(['data' => $latest]);
}

// public function getCurrentTitle()
// {
//     $info = InfoKaryaWisata::orderBy('tanggal', 'desc')->first(); // terbaru

// }

public function getPesertaByJudulTanggal(Request $request)
{
    $request->validate([
        'judul' => 'required|string',
        'tanggal' => 'required|date',
    ]);

    // Cari data info berdasarkan judul dan tanggal
    $info = DB::table('info_karya_wisata')
        ->whereRaw('LOWER(title) = ?', [strtolower($request->judul)])
        ->whereDate('tanggal', $request->tanggal)
        ->first();

    if (!$info) {
        return response()->json([
            'status' => 'error',
            'message' => 'Info Karya Wisata tidak ditemukan'
        ], 404);
    }

    // Ambil data absensi berdasarkan judul yang cocok
    $peserta = DB::table('absensi_karya_wisata as a')
        ->join('users as u', 'a.user_id', '=', 'u.id')
        ->select('u.nama', 'u.kelas', 'a.status', 'a.waktu')
        ->whereRaw('LOWER(a.judul) = ?', [strtolower($info->title)])
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $peserta
    ]);
}


public function getGaleriByJudulTanggal(Request $request)
{
    $request->validate([
        'judul' => 'required|string',
        'tanggal' => 'required|date',
    ]);

    // Cari info berdasarkan judul dan tanggal
    $info = DB::table('info_karya_wisata')
        ->whereRaw('LOWER(title) = ?', [strtolower($request->judul)])
        ->whereDate('tanggal', $request->tanggal)
        ->first();

    if (!$info) {
        return response()->json([
            'status' => 'error',
            'message' => 'Info Karya Wisata tidak ditemukan'
        ], 404);
    }

    // Ambil galeri berdasarkan judul yang sama
    $galeri = DB::table('gallery_karya_wisatas')
        ->whereRaw('LOWER(judul) = ?', [strtolower($info->title)])
        ->whereDate('tanggal', $info->tanggal) // opsional
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $galeri
    ]);
}

}

