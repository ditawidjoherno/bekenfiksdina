<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Pameran;
use App\Models\InfoPameran;
use App\Models\User;
use Illuminate\Support\Facades\DB;

class PameranController extends Controller
{

    public function getPameran(Request $request)
{
    $request->validate([
        'kelas' => 'required|string',
        'tanggal' => 'required|date',
    ]);

    $kelas = $request->kelas;
    $tanggal = $request->tanggal;

    $Pameran = Pameran::with('user')
        ->where('kelas', $kelas)
        ->whereDate('created_at', $tanggal)
        ->get();

    if ($Pameran->isEmpty()) {
        return response()->json([
            'message' => 'Data Pameran tidak ditemukan',
            'kelas' => $kelas,
            'hari_kegiatan' => null,
            'tanggal_kegiatan' => null,
            'batas_pendaftaran' => null,
            'biaya' => null,
            'data' => [],
        ]);
    }

    // Ambil info umum dari record pertama
    $first = $Pameran->first();

    $data = $Pameran->map(function ($item) {
        return [
            'nama' => $item->user->nama,
            'nisn' => $item->user->nisn,
            'status' => $item->status,
            'waktu_daftar' => $item->waktu_daftar,
            'tanggal_daftar' => $item->tanggal_daftar,
        ];
    });

    return response()->json([
        'message' => 'Data Pameran berhasil diambil',
        'kelas' => $kelas,
        'hari_kegiatan' => $first->hari_kegiatan,
        'tanggal_kegiatan' => $first->tanggal_kegiatan,
        'batas_pendaftaran' => $first->batas_pendaftaran,
        'biaya' => $first->biaya,
        'data' => $data,
    ]);
}


public function updateStatusJikaWaktuSelesai($kelas, $tanggal)
{
    $now = date('Y-m-d H:i'); // waktu sekarang lengkap (tanggal + jam)

    // Ambil batas_pendaftaran (diasumsikan datetime) dari DB
    $batasPendaftaran = Pameran::where('kelas', $kelas)
        ->where('tanggal_kegiatan', $tanggal)
        ->value('batas_pendaftaran');

    if (!$batasPendaftaran) {
        return ['error' => 'Batas pendaftaran belum diatur.'];
    }

    if ($now >= $batasPendaftaran) {
        Pameran::where('kelas', $kelas)
            ->where('tanggal_kegiatan', $tanggal)
            ->where('status', '!=', 'Daftar')
            ->update(['status' => 'Tidak Daftar']);

        return ['message' => 'Status Pameran otomatis diupdate menjadi Tidak Daftar.'];
    }

    return ['message' => 'Belum waktunya update status.'];
}


public function inputPameran(Request $request)
{
    // Validasi input
    $request->validate([
        'kelas' => 'required|string',
        'tanggal_kegiatan' => 'required|date',
        'hari_kegiatan' => 'required|string',
        'batas_pendaftaran' => 'required|date',
        'biaya' => 'required|integer',
        'Pameran' => 'required|array',
        'Pameran.*.nisn' => 'required|string',
        'Pameran.*.status' => 'required|in:Daftar,Tidak Daftar',
        'Pameran.*.waktu_daftar' => 'required|string',
        'Pameran.*.tanggal_daftar' => 'required|date',
    ]);

    $kelas = $request->kelas;
    $tanggal_kegiatan = $request->tanggal_kegiatan;
    $hari_kegiatan = $request->hari_kegiatan;
    $batas_pendaftaran = $request->batas_pendaftaran;
    $biaya = $request->biaya;
    $tourData = $request->Pameran;

    foreach ($tourData as $item) {
        $user = User::where('nisn', $item['nisn'])->first();

        if (!$user) {
            \Log::warning("User tidak ditemukan untuk NISN: " . $item['nisn']);
            continue;
        }

        Pameran::updateOrCreate(
            [
                'user_id' => $user->id,
                'kelas' => $kelas,
                'tanggal_kegiatan' => $tanggal_kegiatan,
            ],
            [
                'hari_kegiatan' => $hari_kegiatan,
                'batas_pendaftaran' => $batas_pendaftaran,
                'biaya' => $biaya,
                'status' => $item['status'],
                'tanggal_daftar' => $item['tanggal_daftar'],
                'waktu_daftar' => $item['waktu_daftar'],
            ]
        );
    }

    $this->updateStatusJikaWaktuSelesai($kelas, $tanggal_kegiatan);

    return response()->json([
        'message' => 'Data Pameran berhasil disimpan atau diperbarui.'
    ], 200);
}

public function InfoPameran()
    {
        $info = InfoPameran::latest()->first();
        return response()->json($info);
    }

public function InputInfoPameran(Request $request)
{
    $request->validate([
        'title' => 'required|string',
        'tanggal' => 'required|date',
    ]);

    $info = InfoPameran::create([
        'title' => $request->title,
        'tanggal' => $request->tanggal,
    ]);

    return response()->json([
        'message' => 'Data berhasil disimpan',
        'data' => $info
    ]);
}

public function semuaPesertaPameran()
{
    $data = DB::table('pameran')
        ->join('users', 'pameran.user_id', '=', 'users.id')
        ->join('info_pameran', 'pameran.id', '=', 'info_pameran.id')
        ->select(
            'pameran.id',
            'users.nisn',
            'users.nama as name',
            'users.kelas as class',
            DB::raw("DATE_FORMAT(pameran.created_at, '%h:%i %p') as time"),
            DB::raw("DATE_FORMAT(pameran.created_at, '%d, %b %Y') as date")
        )
        ->orderBy('pameran.created_at', 'asc')
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $data
    ]);
}

}