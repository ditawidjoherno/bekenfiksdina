<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ekskul;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use App\Models\AbsensiEkskul;


class EkskulController extends Controller
{
    public function index()
    {
        return response()->json(Ekskul::all());
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'   => 'required|string',
            'mentor' => 'required|string',
            'image'  => 'nullable|image|max:2048',
            'description' => 'nullable|string', //
        ]);

       if ($request->hasFile('image')) {
    $imagePath = $request->file('image')->store('ekskul_images', 'public');
    $fullImageURL = asset('storage/' . $imagePath); // 👈 hasil URL
} else {
    $fullImageURL = null;
}
        $ekskul = Ekskul::create([
            'name'   => $request->name,
            'mentor' => $request->mentor,
                'image'  => $fullImageURL, // 👈 simpan sebagai URL
            'description' => $request->description ?? '', //
        ]);

        return response()->json($ekskul, 201);
    }


public function uploadPhoto(Request $request)
{
    $validated = $request->validate([
        'id' => 'required|exists:ekskuls,id',
        'image' => 'required|image|max:5120',
    ]);

    $ekskul = Ekskul::find($request->id);

    if (!$ekskul) {
        return response()->json(['message' => 'Ekskul tidak ditemukan'], 404);
    }

 if ($request->hasFile('image')) {
        $path = $request->file('image')->store('ekskul_images', 'public');
        $ekskul->image = 'storage/' . $path; // simpan path relatif
        $ekskul->save();
    }

    return response()->json(['ekskul' => $ekskul], 200);
}


public function show($id)
{
    $ekskul = Ekskul::findOrFail($id);

    return response()->json([
        'id' => $ekskul->id,
        'name' => $ekskul->name,
        'mentor' => $ekskul->mentor,
        'image' => asset($ekskul->image),
    ]);
}
    public function getDescription($id)
    {
        $ekskul = Ekskul::find($id);
        if (!$ekskul) {
            return response()->json(['message' => 'Ekskul tidak ditemukan'], 404);
        }

        return response()->json([
            'description' => $ekskul->description ?? ''
        ]);
    }

   public function updateDescription(Request $request, $id)
{
    $request->validate([
        'description' => 'required|string'
    ]);

    $ekskul = \App\Models\Ekskul::find($id);
    if (!$ekskul) {
        return response()->json(['message' => 'Ekskul tidak ditemukan'], 404);
    }

    $ekskul->description = $request->description;
    $ekskul->save();

    return response()->json(['message' => 'Deskripsi berhasil diperbarui']);
}
public function storeAchievement(Request $request, $id)
{
    $validated = $request->validate([
        'date' => 'required|date',
        'championship' => 'required|string|max:255',
        'event' => 'required|string|max:255',
    ]);

    $ekskul = \App\Models\Ekskul::findOrFail($id);

    $achievement = $ekskul->achievements()->create($validated);

    return response()->json([
        'message' => 'Prestasi berhasil ditambahkan ke ekskul.',
        'data' => $achievement
    ]);
}
public function getAchievements($id)
{
    $ekskul = \App\Models\Ekskul::with('achievements')->find($id);

    if (!$ekskul) {
        return response()->json(['message' => 'Ekskul tidak ditemukan'], 404);
    }

    return response()->json($ekskul->achievements);
}
public function getByName($name)
{
    $ekskul = \App\Models\Ekskul::where('name', $name)->first();

    if (!$ekskul) {
        return response()->json(['message' => 'Ekskul tidak ditemukan'], 404);
    }

    return response()->json($ekskul);
}
public function destroy($id)
{
    $ekskul = Ekskul::find($id);

    if (!$ekskul) {
        return response()->json(['message' => 'Ekskul tidak ditemukan'], 404);
    }

    // Hapus file gambar jika ada
    if ($ekskul->image && str_contains($ekskul->image, 'storage/')) {
        $relativePath = str_replace(asset(''), '', $ekskul->image);
        $filePath = public_path($relativePath);
        if (file_exists($filePath)) {
            unlink($filePath);
        }
    }

    $ekskul->delete();

    return response()->json(['message' => 'Ekskul berhasil dihapus.']);
}
public function statistikEkskul(Request $request)
{
    $ekskulId = $request->query('ekskul_id'); // ID ekskul dari query

    if (!$ekskulId) {
        return response()->json(['message' => 'Ekskul ID wajib dikirim.'], 400);
    }

    // ✅ Hitung jumlah anggota ekskul ini per kelas (tanpa JOIN)
    $anggota = DB::table('anggota_ekskul')
        ->where('ekskul_id', $ekskulId)
        ->select('kelas', DB::raw('count(*) as jumlah_anggota'))
        ->groupBy('kelas')
        ->get()
        ->keyBy('kelas');

    // ✅ Hitung jumlah total siswa per kelas
    $siswa = DB::table('users')
        ->where('role', 'siswa')
        ->select('kelas', DB::raw('count(*) as jumlah_siswa'))
        ->groupBy('kelas')
        ->get();

    // ✅ Gabungkan hasil
    $result = $siswa->map(function ($item) use ($anggota) {
        return [
            'name' => $item->kelas,
            'Anggota' => $anggota[$item->kelas]->jumlah_anggota ?? 0,
            'Siswa' => $item->jumlah_siswa,
        ];
    });

    return response()->json($result);
}

public function riwayatKehadiran($id, Request $request)
{
    $bulan = $request->query('bulan');
    $tahun = $request->query('tahun');

    if (!$bulan || !$tahun) {
        return response()->json(['error' => 'Parameter bulan dan tahun wajib diisi'], 400);
    }

    $riwayat = AbsensiEkskul::where('anggota_id', $id) // ✅ nama foreign key benar
        ->whereMonth('tanggal', $bulan)
        ->whereYear('tanggal', $tahun)
        ->orderBy('tanggal', 'asc')
        ->get(['tanggal', 'status', 'waktu_absen']); // ✅ pakai nama kolom yang benar

    return response()->json($riwayat);
}

}
