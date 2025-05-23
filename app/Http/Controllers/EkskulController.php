<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ekskul;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;


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

}