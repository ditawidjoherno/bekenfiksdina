<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Ekskul;
use Illuminate\Support\Facades\Storage;

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
        ]);

        $imagePath = null;
        if ($request->hasFile('image')) {
            $imagePath = $request->file('image')->store('ekskul_images', 'public');
        }

        $ekskul = Ekskul::create([
            'name'   => $request->name,
            'mentor' => $request->mentor,
            'image'  => $imagePath ? asset('storage/' . $imagePath) : null,
        ]);

        return response()->json($ekskul, 201);
    }
}