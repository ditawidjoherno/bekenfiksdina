<?php

namespace App\Http\Controllers;

use App\Models\StudyTour;
use App\Models\User;
use Illuminate\Http\Request;
use App\Models\InfoStudyTour;
use Illuminate\Support\Facades\DB;
use App\Models\TourGallery;


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
        ->whereDate('created_at', $tanggal)
        ->get();

    if ($studyTour->isEmpty()) {
        return response()->json([
            'message' => 'Data Study Tour tidak ditemukan',
            'kelas' => $kelas,
            'hari_kegiatan' => null,
            'tanggal_kegiatan' => null,
            'batas_pendaftaran' => null,
            'biaya' => null,
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
        'hari_kegiatan' => $first->hari_kegiatan,
        'tanggal_kegiatan' => $first->tanggal_kegiatan,
        'batas_pendaftaran' => $first->batas_pendaftaran,
        'biaya' => $first->biaya,
        'data' => $data,
    ]);
}


public function updateStatusJikaWaktuSelesai($kelas, $tanggal)
{
    $now = date('Y-m-d H:i'); 

    $batasPendaftaran = StudyTour::where('kelas', $kelas)
        ->where('tanggal_kegiatan', $tanggal)
        ->value('batas_pendaftaran');

    if (!$batasPendaftaran) {
        return ['error' => 'Batas pendaftaran belum diatur.'];
    }

    if ($now >= $batasPendaftaran) {
        StudyTour::where('kelas', $kelas)
            ->where('tanggal_kegiatan', $tanggal)
            ->where('status', '!=', 'Daftar')
            ->update(['status' => 'Tidak Daftar']);

        return ['message' => 'Status Study Tour otomatis diupdate menjadi Tidak Daftar.'];
    }

    return ['message' => 'Belum waktunya update status.'];
}


public function inputStudyTour(Request $request)
{
    $request->validate([
        'kelas' => 'required|string',
        'tanggal_kegiatan' => 'required|date',
        'hari_kegiatan' => 'required|string',
        'batas_pendaftaran' => 'required|date',
        'biaya' => 'required|integer',
        'study_tour_info_id' => 'required|exists:study_tour_info,id',
        'studyTour' => 'required|array',
        'studyTour.*.nisn' => 'required|string',
        'studyTour.*.status' => 'required|in:Daftar,Tidak Daftar',
        'studyTour.*.waktu_daftar' => 'required|string',
        'studyTour.*.tanggal_daftar' => 'required|date',
    ]);

    $kelas = $request->kelas;
    $tanggal_kegiatan = $request->tanggal_kegiatan;
    $hari_kegiatan = $request->hari_kegiatan;
    $batas_pendaftaran = $request->batas_pendaftaran;
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
                'tanggal_kegiatan' => $tanggal_kegiatan,
            ],
            [
                'study_tour_info_id' => $request->study_tour_info_id,
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
        'message' => 'Data Study Tour berhasil disimpan atau diperbarui.'
    ], 200);
}

public function InfoStudyTour()
    {
        $info = InfoStudyTour::latest()->first();
        return response()->json($info);
    }

public function InputInfoTour(Request $request)
{
    $request->validate([
        'title' => 'required|string',
        'tanggal' => 'required|date',
    ]);

    $info = InfoStudyTour::create([
        'title' => $request->title,
        'tanggal' => $request->tanggal,
    ]);

    return response()->json([
        'message' => 'Data berhasil disimpan',
        'data' => $info
    ]);
}

public function semuaPesertaStudyTour(Request $request)
{
    $query = DB::table('study_tour')
        ->join('users', 'study_tour.user_id', '=', 'users.id')
        ->leftJoin('study_tour_info', 'study_tour.study_tour_info_id', '=', 'study_tour_info.id')
        ->select(
            'study_tour.id',
            'users.nisn',
            'users.nama as name',
            'users.kelas as class',
            'study_tour_info.title as title',
            'study_tour_info.tanggal as event_date',
            DB::raw("DATE_FORMAT(study_tour.created_at, '%H:%i:%s') as time"),
            DB::raw("DATE_FORMAT(study_tour.created_at, '%Y-%m-%d') as date")
        )
        ->orderBy('study_tour_info.tanggal', 'desc');

    // 🔽 Tambahkan filter jika ada parameter ID
    if ($request->has('id')) {
        $query->where('study_tour.study_tour_info_id', $request->id);
    }

    $data = $query->distinct()->get();

    return response()->json([
        'status' => 'success',
        'data' => $data
    ]);
}



public function show($id)
{
    $tour = \App\Models\StudyTour::findOrFail($id);

    return response()->json([
        'id' => $tour->id,
        'judul' => $tour->judul,
        'foto_perjalanan' => $tour->foto_perjalanan,
    ]);
}
public function getGallery($id)
{
    try {
        $images = TourGallery::where('study_tour_id', $id)->pluck('image_path');

        return response()->json([
            'images' => $images
        ]);
    } catch (\Throwable $e) {
        return response()->json([
            'error' => $e->getMessage(),
            'line' => $e->getLine(),
        ], 500);
    }
}
public function uploadGallery(Request $request, $id)
{
    $request->validate([
        'image' => 'required|image|max:2048',
    ]);

    $file = $request->file('image');
    $path = $file->store('study_tour_gallery', 'public');

    $gallery = TourGallery::create([
        'study_tour_id' => $id,
        'image_path' => 'storage/' . $path,
    ]);

    return response()->json([
        'message' => 'Foto berhasil diunggah',
        'image' => $gallery->image_path,
    ]);
}
public function deleteGalleryImage(Request $request, $id)
{
    $request->validate([
        'image_path' => 'required|string'
    ]);

    $relativePath = str_replace('storage/', '', $request->image_path);

    if (\Storage::disk('public')->exists($relativePath)) {
        \Storage::disk('public')->delete($relativePath);
    }

    TourGallery::where('study_tour_id', $id)
        ->where('image_path', $request->image_path)
        ->delete();

    return response()->json(['message' => 'Gambar berhasil dihapus']);
}
public function PerjalananSebelumnya()
{
    $data = InfoStudyTour::select('id', 'title', 'tanggal', 'location')
        ->orderByDesc('tanggal')
        ->get();

    return response()->json($data);
}
public function InfoStudyTourById($id)
{
    $info = InfoStudyTour::find($id);

    if (!$info) {
        return response()->json(['error' => 'Data tidak ditemukan'], 404);
    }

    return response()->json([
        'title' => $info->title,
        'tanggal' => $info->tanggal,
    ]);
}

}