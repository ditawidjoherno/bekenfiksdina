<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use Illuminate\Http\Request;

class AchievementController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'championship' => 'required|string|max:255',
            'event' => 'required|string|max:255',
        ]);

        $achievement = Achievement::create($validated);

        return response()->json([
            'message' => 'Prestasi berhasil ditambahkan.',
            'data' => $achievement
        ], 201);
    }
    public function update(Request $request, $id)
    {
        $validated = $request->validate([
            'date' => 'required|date',
            'championship' => 'required|string|max:255',
            'event' => 'required|string|max:255',
        ]);

        $achievement = Achievement::findOrFail($id);
        $achievement->update($validated);

        return response()->json([
            'message' => 'Prestasi berhasil diperbarui.',
            'data' => $achievement
        ]);
    }

    public function destroy($id)
    {
        $achievement = Achievement::findOrFail($id);
        $achievement->delete();

        return response()->json([
            'message' => 'Prestasi berhasil dihapus.'
        ]);
    }
}
