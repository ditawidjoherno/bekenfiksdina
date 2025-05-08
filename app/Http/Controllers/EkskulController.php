<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Ekskul;

class EkskulController extends Controller
{
    public function store(Request $request)
    {
        // Validasi input
        $request->validate([
            'nama_ekskul' => 'required|string|max:255',
            'penanggung_jawab' => 'required|string|max:255',
            'cover_gambar' => 'required|file|mimes:jpg,jpeg,png|max:2048',
        ]);
    
        // Simpan file gambar ke storage/app/public/ekskul
        if ($request->hasFile('cover_gambar')) {
            $file = $request->file('cover_gambar');
            $filename = time() . '_' . $file->getClientOriginalName();
            $path = $file->storeAs('public/ekskul', $filename);
        }
    
        // Simpan data ke database (contoh pakai model Ekskul)
        $ekskul = new Ekskul();
        $ekskul->nama_ekskul = $request->nama_ekskul;
        $ekskul->penanggung_jawab = $request->penanggung_jawab;
        $ekskul->cover_gambar = $filename ?? null;
        $ekskul->save();
    
        return response()->json([
            'message' => 'Ekskul berhasil ditambahkan',
            'data' => $ekskul
        ], 201);
    }
    
}
