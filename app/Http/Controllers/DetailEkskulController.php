<?php

namespace App\Http\Controllers;

use App\Models\DetailEkskul;
use Illuminate\Http\Request;

class DetailEkskulController extends Controller
{
    public function index()
    {
        return DetailEkskul::with(['ekskul', 'anggota'])->get();
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'ekskul_id' => 'required|exists:ekskul,id',
            'anggota_user_id' => 'required|exists:users,id',
            'deskripsi' => 'nullable|string',
            'informasi_ekskul' => 'nullable|string',
            'capaian_prestasi' => 'nullable|string',
        ]);

        $detail = DetailEkskul::create($validated);

        return response()->json([
            'message' => 'Detail ekskul berhasil ditambahkan',
            'data' => $detail
        ], 201);
    }

    public function show($id)
    {
        $detail = DetailEkskul::with(['ekskul', 'anggota'])->findOrFail($id);
        return response()->json($detail);
    }

    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'ekskul_id' => 'sometimes|exists:ekskul,id',
            'anggota_user_id' => 'sometimes|exists:users,id',
            'deskripsi' => 'nullable|string',
            'informasi_ekskul' => 'nullable|string',
            'capaian_prestasi' => 'nullable|string',
        ]);

        $detail = DetailEkskul::findOrFail($id);
        $detail->update($validated);

        return response()->json([
            'message' => 'Detail ekskul berhasil diperbarui',
            'data' => $detail
        ]);
    }

    public function destroy($id)
    {
        $detail = DetailEkskul::findOrFail($id);
        $detail->delete();

        return response()->json(['message' => 'Detail ekskul berhasil dihapus']);
    }
}
