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
                'title' => null,
                'data' => [],
            ]);
        }

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
            'title' => $first->title,
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
        $request->validate([
            'kelas' => 'required|string',
            'tanggal' => 'required|date',
            'hari' => 'required|string',
            'mulai' => 'required|string',
            'selesai' => 'required|date',
            'tujuan' => 'required|string',
            'biaya' => 'required|integer',
            'title' => 'nullable|string',
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
        $title = $request->title;
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
                    'title' => $title,
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
   public function storeInfo(Request $request)
{
    $request->validate([
        'kelas' => 'required|string',
        'tanggal' => 'required|date',
        'hari' => 'required|string',
        'mulai' => 'required|string',
        'selesai' => 'required|string',
        'tujuan' => 'required|string',
        'biaya' => 'required|integer',
        'title' => 'nullable|string',
    ]);

    $info = StudyTour::firstOrNew([
        'kelas' => $request->kelas,
        'tanggal' => $request->tanggal,
        'user_id' => 1, // ✅ isi user_id default
    ]);

    $info->hari = $request->hari;
    $info->mulai = $request->mulai;
    $info->selesai = $request->selesai;
    $info->tujuan = $request->tujuan;
    $info->biaya = $request->biaya;
    $info->title = $request->title;

    // ✅ Tambahkan nilai default agar tidak error
    $info->status = 'Daftar';
    $info->waktu_daftar = now()->format('H:i:s');
    $info->tanggal_daftar = now()->toDateString();

    $info->save();

    return response()->json([
        'message' => 'Informasi Study Tour berhasil disimpan.',
        'data' => $info
    ], 200);
}
public function getInfo(Request $request)
{
    $data = StudyTour::latest()->first(); // atau where kelas = '7A' jika ingin spesifik
    return response()->json($data);
}
public function listTour()
{
    $tours = StudyTour::with('user')->orderBy('tanggal', 'desc')->get();

    $grouped = $tours->groupBy(function ($item) {
        return $item->kelas . '-' . $item->tanggal;
    });

    $result = $grouped->map(function ($items, $key) {
        $first = $items->first();
        return [
            'kelas' => $first->kelas,
            'tanggal' => $first->tanggal,
            'hari' => $first->hari,
            'mulai' => $first->mulai,
            'selesai' => $first->selesai,
            'tujuan' => $first->tujuan,
            'biaya' => $first->biaya,
            'title' => $first->title,
            'siswa' => $items->map(function ($item) {
                return [
                    'nama' => $item->user->nama,
                    'nisn' => $item->user->nisn,
                    'status' => $item->status,
                    'waktu_daftar' => $item->waktu_daftar,
                    'tanggal_daftar' => $item->tanggal_daftar,
                ];
            }),
        ];
    })->values();

    return response()->json([
        'message' => 'List Study Tour berhasil diambil',
        'data' => $result
    ]);
}

}
