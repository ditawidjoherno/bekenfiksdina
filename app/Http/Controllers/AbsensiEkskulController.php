<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\AbsensiEkskul;
use Illuminate\Support\Facades\DB;
use App\Models\AbsensiEkskulHeader; // ✅ yang bena


class AbsensiEkskulController extends Controller
{
    public function index(Request $request)
    {
        $ekskul_id = $request->query('ekskul_id');
        $tanggal = $request->query('tanggal');

        $absensi = AbsensiEkskul::where('ekskul_id', $ekskul_id)
            ->where('tanggal', $tanggal)
            ->get();

        return response()->json($absensi);
    }

    public function store(Request $request)
{
    // ✅ VALIDASI
    $validated = $request->validate([
        'ekskul_id' => 'required|integer|exists:ekskuls,id',
        'tanggal' => 'required|date',
        'kegiatan' => 'required|string',
        'mulai' => 'required|string',
        'selesai' => 'required|string',
        'absensi' => 'required|array',
        'absensi.*.anggota_id' => 'required|integer|exists:anggota_ekskul,id',
        'absensi.*.status' => 'required|string',
        'absensi.*.waktu_absen' => 'nullable|string',
    ]);

    // ✅ SIMPAN HEADER
    AbsensiEkskulHeader::updateOrCreate(
        [
            'ekskul_id' => $validated['ekskul_id'],
            'tanggal' => $validated['tanggal']
        ],
        [
            'kegiatan' => $validated['kegiatan'],
            'mulai' => $validated['mulai'],
            'selesai' => $validated['selesai'],
        ]
    );

    // ✅ HAPUS DATA LAMA ABSENSI
    AbsensiEkskul::where('ekskul_id', $validated['ekskul_id'])
        ->where('tanggal', $validated['tanggal'])
        ->delete();

    // ✅ SIMPAN ABSENSI BARU
    foreach ($validated['absensi'] as $item) {
    AbsensiEkskul::create([
        'ekskul_id' => $validated['ekskul_id'],
        'tanggal' => $validated['tanggal'],
        'anggota_id' => $item['anggota_id'],
        'status' => $item['status'],
        'waktu_absen' => isset($item['waktu_absen'])
            ? date('H:i:s', strtotime($item['waktu_absen']))
            : now()->format('H:i:s'),
    ]);
}

    return response()->json(['message' => 'Absensi berhasil disimpan']);
}


//     public function getAbsensiEkskul(Request $request)
// {
//     $ekskulId = $request->query('ekskul_id');
//     $tanggal = $request->query('tanggal');

//     $header = AbsensiEkskul::where('ekskul_id', $ekskulId)
//     ->where('tanggal', $tanggal)
//     ->whereNotNull('kegiatan')
//     ->orderBy('id', 'asc') // ambil baris pertama yang valid
//     ->first();

//     $absensi = AbsensiEkskul::where('ekskul_id', $ekskulId)
//     ->where('tanggal', $tanggal)
//     ->get([
//         'anggota_id',
//         'status',
//         DB::raw('waktu_edit as waktu_absen') // ✅ penting
//     ]);

//     return response()->json([
//         'kegiatan' => $header?->kegiatan ?? null,
//         'mulai'    => $header?->mulai ?? null,
//         'selesai'  => $header?->selesai ?? null,
//         'absensi'  => $absensi ?? [],
//     ]);
// }
public function getAbsensiHeader(Request $request)
{
    $ekskulId = $request->query('ekskul_id');
    $tanggal = $request->query('tanggal');

    if (!$ekskulId || !$tanggal) {
        return response()->json(['message' => 'Parameter tidak lengkap'], 400);
    }

    // ✅ Ambil header (dari tabel absensi_ekskul_headers)
    $header = AbsensiEkskulHeader::where('ekskul_id', $ekskulId)
        ->where('tanggal', $tanggal)
        ->first();

    // ✅ Ambil absensi (dari tabel absensi_ekskuls)
    $absensi = AbsensiEkskul::where('ekskul_id', $ekskulId)
        ->where('tanggal', $tanggal)
        ->get([
            'anggota_id',
            'status',
            DB::raw('waktu_edit as waktu_absen')
        ]);

    return response()->json([
        'kegiatan' => $header?->kegiatan ?? '-',
        'mulai'    => $header?->mulai ?? '-',
        'selesai'  => $header?->selesai ?? '-',
        'absensi'  => $absensi
    ]);
}

public function rekapPerTanggal(Request $request)
{
    try {
        $ekskulId = $request->query('ekskul_id');
        $tanggal = $request->query('tanggal');

        if (!$ekskulId || !$tanggal) {
            return response()->json(['message' => 'Parameter tidak lengkap'], 400);
        }

        $rekap = \App\Models\AbsensiEkskul::where('ekskul_id', $ekskulId)
    ->where('tanggal', $tanggal)
    ->select(
        'status',
        DB::raw('count(*) as jumlah')
    )
    ->groupBy('status')
    ->get();

        // Jangan gunakan groupBy Laravel Collection
        return response()->json([
            'status' => 'success',
            'data' => $rekap
        ]);

    } catch (\Throwable $e) {
        return response()->json([
            'status' => 'error',
            'message' => 'Gagal rekap',
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
        ], 500);
    }
}

}
