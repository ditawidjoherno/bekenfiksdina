<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Event;

class EventController extends Controller
{
    // Ambil semua event
    public function index()
    {
        return response()->json(Event::all());
    }

    // Tambah atau update event
    public function store(Request $request)
    {
        $request->validate([
            'date' => 'required|date',
            'note' => 'required|string|max:255',
        ]);

        $event = Event::updateOrCreate(
            ['date' => $request->date],
            ['note' => $request->note]
        );

        return response()->json($event);
    }

    // Hapus event berdasarkan tanggal
    public function destroy($date)
    {
        $event = Event::where('date', $date)->first();

        if (!$event) {
            return response()->json(['message' => 'Event not found'], 404);
        }

        $event->delete();

        return response()->json(['message' => 'Event deleted']);
    }
}