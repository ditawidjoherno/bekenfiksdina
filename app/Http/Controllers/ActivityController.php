<?php

namespace App\Http\Controllers;

use App\Models\UserActivity;
use Illuminate\Http\Request;

class ActivityController extends Controller
{
    public function index()
    {
        $activities = UserActivity::with('user')
            ->orderByDesc('created_at')
            ->take(10)
            ->get()
            ->map(function ($activity) {
                return [
                    'date' => $activity->created_at->format('M d, Y'),
                    'time' => $activity->created_at->format('h.i a'),
                    'name' => $activity->user->nama,
                    'role' => ucfirst($activity->user->role),
                    'action' => $activity->action,
                    'avatar' => $activity->user->role === 'guru' ? '/images/profiladmin.jpg' : '/images/profilsiswa.jpg',
                    'color' => $activity->user->role === 'guru' ? 'bg-pink-300' : 'bg-purple-300',
                    'textColor' => 'text-black',
                    'actionColor' => 'text-black',
                    'roleColor' => $activity->user->role === 'guru' ? 'text-[#EC4899]' : 'text-[#7E22CE]',
                ];
            });

        return response()->json($activities);
    }

    // Method untuk menyimpan data user activity baru
    public function store(Request $request)
    {
        $validated = $request->validate([
            'user_id' => 'required|exists:users,id',
            'action' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $activity = UserActivity::create($validated);

        return response()->json([
            'message' => 'User activity created successfully',
            'data' => $activity,
        ], 201);
    }
}
