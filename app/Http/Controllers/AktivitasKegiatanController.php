<?php

namespace App\Http\Controllers;

use App\Models\AktivitasKegiatan;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AktivitasKegiatanController extends Controller
{
    // Menampilkan semua data aktivitas
    public function index()
    {
        $kegiatan = AktivitasKegiatan::with('penanggungJawab')->get();
        return response()->json($kegiatan);
    }

    // Menyimpan aktivitas baru
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'nama_kegiatan' => 'required|string|max:255',
            'kategori' => 'required|string|max:100',
            'start' => 'required|date',
            'end' => 'required|date|after_or_equal:start',
            'tipe' => 'required|string|max:50',
            'foto' => 'nullable|string',
            'total_days_left' => 'nullable|integer',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:users,id', // Validasi peserta
            'penanggung_jawab_id' => 'required|exists:users,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        // Simpan kegiatan
        $kegiatan = AktivitasKegiatan::create([
            'nama_kegiatan' => $request->nama_kegiatan,
            'kategori' => $request->kategori,
            'start' => $request->start,
            'end' => $request->end,
            'tipe' => $request->tipe,
            'foto' => $request->foto,
            'total_days_left' => $request->total_days_left,
            'participants' => $request->participants, // sudah berupa array
            'penanggung_jawab_id' => $request->penanggung_jawab_id,
        ]);
        
    
        // Simpan relasi peserta (jika ada)
        if ($request->has('participants')) {
            $kegiatan->participants = $request->participants;
            $kegiatan->save();            
        }
    
        return response()->json($kegiatan, 201);
    }
    
    
    // Menampilkan detail aktivitas berdasarkan ID
    public function show($id)
    {
        $kegiatan = AktivitasKegiatan::with('penanggungJawab')->find($id);

        if (!$kegiatan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        return response()->json($kegiatan);
    }

    // Mengupdate data aktivitas
    public function update(Request $request, $id)
    {
        $kegiatan = AktivitasKegiatan::find($id);
    
        if (!$kegiatan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }
    
        $validator = Validator::make($request->all(), [
            'nama_kegiatan' => 'sometimes|string|max:255',
            'kategori' => 'sometimes|string|max:100',
            'start' => 'sometimes|date',
            'end' => 'sometimes|date|after_or_equal:start',
            'tipe' => 'sometimes|string|max:50',
            'foto' => 'nullable|string',
            'total_days_left' => 'nullable|integer',
            'participants' => 'nullable|array',
            'participants.*' => 'exists:users,id', // Validasi peserta
            'penanggung_jawab_id' => 'sometimes|exists:users,id',
        ]);
    
        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }
    
        // Update kegiatan
        $kegiatan->update($request->all());
    
        // Update relasi peserta (jika ada)
        if ($request->has('participants')) {
        }
    
        return response()->json($kegiatan);
    }
    

    // Menghapus data aktivitas
    public function destroy($id)
    {
        $kegiatan = AktivitasKegiatan::find($id);

        if (!$kegiatan) {
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $kegiatan->delete();

        return response()->json(['message' => 'Data berhasil dihapus']);
    }

  // Mendapatkan daftar peserta dari suatu aktivitas
public function getPeserta($id)
{
    $kegiatan = AktivitasKegiatan::find($id);

    if (!$kegiatan) {
        return response()->json(['message' => 'Data tidak ditemukan'], 404);
    }

    $participantIds = $kegiatan->participants ?? [];

    $peserta = User::whereIn('id', $participantIds)->get();

    return response()->json($peserta);
}

}
