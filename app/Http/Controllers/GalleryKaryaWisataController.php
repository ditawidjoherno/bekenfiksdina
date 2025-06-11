<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\GalleryKaryaWisata;

class GalleryKaryaWisataController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'judul' => 'required|string',
            'tanggal' => 'required|date',
            'files.*' => 'required|image|mimes:jpeg,jpg,png|max:2048',
        ]);

        $slug = Str::slug($request->judul);
        $folder = "gallery/{$slug}_{$request->tanggal}";

        $urls = [];

        foreach ($request->file('files') as $file) {
            $filename = uniqid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs($folder, $filename, 'public');
            $url = Storage::url($path);

            GalleryKaryaWisata::create([
                'judul' => $request->judul,
                'tanggal' => $request->tanggal,
                'url' => $url,
            ]);

            $urls[] = $url;
        }

        return response()->json([
            'status' => 'success',
            'files' => $urls
        ]);
    }

    public function getByJudulTanggal(Request $request)
{
    $request->validate([
        'judul' => 'required|string',
        'tanggal' => 'required|date',
    ]);

   $data = GalleryKaryaWisata::where('judul', $request->judul)
    ->where('tanggal', $request->tanggal)
    ->select('id', 'url')
    ->get();

    return response()->json([
        'status' => 'success',
        'data' => $data
    ]);
}
public function destroy($id)
{
    $gallery = GalleryKaryaWisata::findOrFail($id);

    // Hapus file fisik
    if (\Storage::disk('public')->exists(str_replace('/storage/', '', $gallery->url))) {
        \Storage::disk('public')->delete(str_replace('/storage/', '', $gallery->url));
    }

    $gallery->delete();

    return response()->json(['status' => 'success']);
}
    
}
