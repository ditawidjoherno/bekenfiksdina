<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\PameranGallery;

class PameranGalleryController extends Controller
{
    // Ambil semua gambar berdasarkan pameran_id
    public function index($id)
    {
        $images = PameranGallery::where('pameran_id', $id)->pluck('image_path');
        return response()->json(['images' => $images]);
    }

    // Upload gambar ke storage dan simpan path-nya ke DB
    public function upload(Request $request, $id)
    {
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $urls = [];

        foreach ($request->file('images') as $image) {
            $filename = 'pameran_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/pameran_galleries', $filename);
            $url = Storage::url($path);

            PameranGallery::create([
                'pameran_id' => $id,
                'image_path' => $url,
            ]);

            $urls[] = $url;
        }

        return response()->json(['urls' => $urls]);
    }

    // Hapus gambar dari storage dan database
    public function delete(Request $request, $id)
    {
        $request->validate([
            'image_url' => 'required|string',
        ]);

        $imageUrl = $request->image_url;
        $imageRecord = PameranGallery::where('pameran_id', $id)
                                     ->where('image_path', $imageUrl)
                                     ->first();

        if (!$imageRecord) {
            return response()->json(['message' => 'Gambar tidak ditemukan'], 404);
        }

        $storagePath = str_replace('/storage/', 'public/', $imageUrl);
        Storage::delete($storagePath);

        $imageRecord->delete();

        return response()->json(['message' => 'Gambar berhasil dihapus']);
    }
}
