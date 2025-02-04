<?php

namespace App\Http\Controllers;

use App\Models\Reminder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReminderController extends Controller
{
    public function addReminder(Request $request)
    {
        $validatedData = $request->validate([
            'task' => 'required|string|max:255',
            'deadline' => 'required|date',
        ]);

        $reminder = Reminder::create([
            'task' => $validatedData['task'],
            'deadline' => $validatedData['deadline'],
            'done' => false,
            'user_id' => Auth::id(),
        ]);

        return response()->json([
            'message' => 'Reminder berhasil ditambahkan!',
            'data' => $reminder,
        ], 201);
    }

    public function getUserReminders()
    {
        $userId = Auth::id();

        $reminders = Reminder::where('user_id', $userId)->get();

        return response()->json([
            'message' => 'Reminder berhasil ditampilkan!',
            'data' => $reminders,
        ]);
    }

    public function deleteReminder($id)
    {
        $userId = Auth::id();

        $reminder = Reminder::where('id', $id)->where('user_id', $userId)->first();

        if ($reminder) {
            $reminder->delete();

            return response()->json([
                'message' => 'Reminder berhasil dihapus!'
            ], 200);
        } else {
            return response()->json([
                'message' => 'Reminder tidak ditemukan atau bukan milik Anda!'
            ], 404);
        }
    }
}
