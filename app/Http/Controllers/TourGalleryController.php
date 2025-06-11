<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\TourGallery;

class TourGalleryController extends Controller
{
    // Ambil semua gambar berdasarkan study_tour_id
    public function index($id)
    {
        $images = TourGallery::where('study_tour_id', $id)->pluck('image_path');
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
            $filename = 'tour_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $path = $image->storeAs('public/tour_galleries', $filename);
            $url = Storage::url($path);

            TourGallery::create([
                'study_tour_id' => $id,
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
        $imageRecord = TourGallery::where('study_tour_id', $id)
                                  ->where('image_path', $imageUrl)
                                  ->first();

        if (!$imageRecord) {
            return response()->json(['message' => 'Gambar tidak ditemukan'], 404);
        }

        // Hapus file fisik
        $storagePath = str_replace('/storage/', 'public/', $imageUrl);
        Storage::delete($storagePath);

        // Hapus record DB
        $imageRecord->delete();

        return response()->json(['message' => 'Gambar berhasil dihapus']);
    }
}
