<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\EkskulGallery;
use Illuminate\Support\Facades\Storage;

class EkskulGalleryController extends Controller
{
    // Ambil galeri berdasarkan ID ekskul
    public function index($id)
    {
        $galeri = EkskulGallery::where('ekskul_id', $id)->latest()->get();

        $data = $galeri->map(function ($item) {
            return [
                'id' => $item->id,
                'imageUrl' => asset('storage/' . $item->image_path),
                'description' => $item->description,
                'uploaded_at' => $item->uploaded_at,
            ];
        });

        return response()->json($data);
    }

    // Upload dan simpan gambar ke galeri
    public function store(Request $request, $id)
    {
        $request->validate([
            'image' => 'required|image|max:2048',
            'description' => 'nullable|string',
        ]);

        $path = $request->file('image')->store('galeri_ekskul', 'public');

        $entry = EkskulGallery::create([
            'ekskul_id'   => $id,
            'image_path'  => $path,
            'description' => $request->description,
            'uploaded_at' => now(),
        ]);

        return response()->json([
    'message' => 'Foto berhasil diunggah.',
    'data' => [
        'id' => $entry->id,
        'imageUrl' => asset('storage/' . $entry->image_path),
        'description' => $entry->description,
        'uploaded_at' => $entry->uploaded_at->format('Y-m-d H:i:s'),
    ],
], 201);

    }

    // Hapus gambar dari galeri
    public function destroy($id)
    {
        $entry = EkskulGallery::findOrFail($id);

        // Hapus file dari storage
        Storage::disk('public')->delete($entry->image_path);

        // Hapus entri dari database
        $entry->delete();

        return response()->json(['message' => 'Foto berhasil dihapus.']);
    }
}
