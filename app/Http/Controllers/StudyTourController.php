<?php

namespace App\Http\Controllers;

use App\Models\StudyTour;
use App\Models\User;
use Illuminate\Http\Request;

class StudyTourController extends Controller
{
public function getStudyTour(Request $request)
{
    $request->validate([
        'kelas' => 'required|string',
        'tanggal' => 'required|date',
    ]);

    $kelas = $request->kelas;
    $tanggal = $request->tanggal;

    $studyTour = StudyTour::with('user')
        ->where('kelas', $kelas)
        ->where('tanggal', $tanggal)
        ->get();

    if ($studyTour->isEmpty()) {
        return response()->json([
            'message' => 'Data Study Tour tidak ditemukan',
            'kelas' => $kelas,
            'hari' => null,
            'mulai' => null,
            'selesai' => null,
            'tujuan' => null,
            'biaya' => null,
            'data' => [],
        ]);
    }

    // Ambil info umum dari record pertama
    $first = $studyTour->first();

    $data = $studyTour->map(function ($item) {
        return [
            'nama' => $item->user->nama,
            'nisn' => $item->user->nisn,
            'status' => $item->status,
            'waktu_daftar' => $item->waktu_daftar,
            'tanggal_daftar' => $item->tanggal_daftar,
        ];
    });

    return response()->json([
        'message' => 'Data Study Tour berhasil diambil',
        'kelas' => $kelas,
        'hari' => $first->hari,
        'mulai' => $first->mulai,
        'selesai' => $first->selesai,
        'tujuan' => $first->tujuan,
        'biaya' => $first->biaya,
        'data' => $data,
    ]);
}


    public function updateStatusJikaWaktuSelesai($kelas, $tanggal)
    {
        $now = date('H:i');

        $jamSelesai = StudyTour::where('kelas', $kelas)
            ->where('tanggal', $tanggal)
            ->value('selesai');

        if (!$jamSelesai) {
            return response()->json(['message' => 'Jam selesai belum diatur.'], 400);
        }

        if ($now >= $jamSelesai) {
            StudyTour::where('kelas', $kelas)
                ->where('tanggal', $tanggal)
                ->where('status', '!=', 'Daftar')
                ->update(['status' => 'Tidak Daftar']);

            return response()->json(['message' => 'Status Study Tour otomatis diupdate menjadi Tidak Daftar.']);
        }

        return response()->json(['message' => 'Belum waktunya update status.']);
    }

public function inputStudyTour(Request $request)
{
    // Validasi input
    $request->validate([
        'kelas' => 'required|string',
        'tanggal' => 'required|date',
        'hari' => 'required|string',
        'mulai' => 'required|string',
        'selesai' => 'required|string',
        'tujuan' => 'required|string',
        'biaya' => 'required|integer',
        'studyTour' => 'required|array',
        'studyTour.*.nisn' => 'required|string',
        'studyTour.*.status' => 'required|string',
        'studyTour.*.waktu_daftar' => 'required|string',
        'studyTour.*.tanggal_daftar' => 'required|date',
    ]);

    $kelas = $request->kelas;
    $tanggal = $request->tanggal;
    $hari = $request->hari;
    $mulai = $request->mulai;
    $selesai = $request->selesai;
    $tujuan = $request->tujuan;
    $biaya = $request->biaya;
    $tourData = $request->studyTour;

    foreach ($tourData as $item) {
        $user = User::where('nisn', $item['nisn'])->first();

        if (!$user) {
            \Log::warning("User tidak ditemukan untuk NISN: " . $item['nisn']);
            continue;
        }

        StudyTour::updateOrCreate(
            [
                'user_id' => $user->id,
                'kelas' => $kelas,
                'tanggal' => $tanggal,
            ],
            [
                'hari' => $hari,
                'mulai' => $mulai,
                'selesai' => $selesai,
                'tujuan' => $tujuan,
                'biaya' => $biaya,
                'status' => $item['status'],
                'tanggal_daftar' => $item['tanggal_daftar'],
                'waktu_daftar' => $item['waktu_daftar'],
            ]
        );
    }

    $this->updateStatusJikaWaktuSelesai($kelas, $tanggal);

    return response()->json([
        'message' => 'Data Study Tour berhasil disimpan atau diperbarui.'
    ], 200);
}
}