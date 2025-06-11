<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\IkutSertaKaryaWisata;
use App\Models\InfoKaryaWisata;

class IkutSertaKaryaWisataController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'kelas' => 'required|string',
            'biaya' => 'required|string',
            'batas_pendaftaran' => 'required|date',
        ]);

        $info = InfoKaryaWisata::first();

        if (!$info) {
            return response()->json([
                'status' => 'error',
                'message' => 'Info Karya Wisata belum tersedia',
            ], 400);
        }

        $data = IkutSertaKaryaWisata::create([
            'kelas' => $validated['kelas'],
            'tanggal_kegiatan' => $info->tanggal,
            'judul' => 'Karya Wisata',
            'biaya' => $validated['biaya'],
            'batas_pendaftaran' => $validated['batas_pendaftaran'],
        ]);

        return response()->json([
            'status' => 'success',
            'data' => $data,
        ]);
    }
    public function show(Request $request)
{
    $kelas = $request->query('kelas');
    $data = IkutSertaKaryaWisata::where('kelas', $kelas)->first();

    return response()->json([
        'status' => 'success',
        'data' => $data
    ]);
}

}

