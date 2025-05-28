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
            $user = $activity->user;

            return [
                'date' => $activity->created_at->format('M d, Y'),
                'time' => $activity->created_at->format('h.i a'),
                'name' => $user->nama,
                'role' => ucfirst($user->role),
                'action' => $activity->action,
                'avatar' => $user->foto_profil
                    ? asset('storage/' . $user->foto_profil)
                    : ($user->role === 'guru'
                        ? asset('/images/profiladmin.jpg')
                        : asset('/images/profilsiswa.jpg')),
                'color' => $user->role === 'guru' ? 'bg-pink-300' : 'bg-purple-300',
                'textColor' => 'text-black',
                'actionColor' => 'text-black',
                'roleColor' => $user->role === 'guru' ? 'text-[#EC4899]' : 'text-[#7E22CE]',
            ];
        });

    return response()->json($activities);
}

}
