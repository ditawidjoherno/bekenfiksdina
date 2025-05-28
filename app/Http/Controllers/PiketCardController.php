<?php

namespace App\Http\Controllers;
use Illuminate\Http\Request;
use App\Models\PiketCard;
use Illuminate\Support\Carbon;

class PiketCardController extends Controller
{
    public function getPiketCard()
{
    $card = PiketCard::latest('tanggal')->first(); // ⬅️ ambil yang terbaru berdasarkan tanggal

    if (!$card) {
        return response()->json([
            'kelas' => '-',
            'tanggal' => null,
            'image_url' => asset('images/piketcard.png'),
        ]);
    }

    return response()->json([
        'kelas' => $card->kelas,
        'tanggal' => $card->tanggal, // ⬅️ jangan pakai formatted string
        'image_url' => asset('images/piketcard.png'),
    ]);
}

    public function store(Request $request)
{
    $validated = $request->validate([
        'tanggal' => 'required|date',
        'kelas' => 'required|string'
    ]);

    PiketCard::create($validated);

    return response()->json(['message' => 'Jadwal berhasil disimpan.']);
}


}
