<?php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\InformasiUmum;

class InformasiUmumController extends Controller
{
    public function index()
    {
        return response()->json(InformasiUmum::orderBy('date', 'desc')->get());
    }

    public function store(Request $request)
{
    \Log::info('📥 Data diterima dari frontend:', $request->all());

    $validated = $request->validate([
        'date' => 'required|date',
        'title' => 'required|string|max:255',
        'text' => 'required|string',
        'author' => 'nullable|string',
        'photo' => 'nullable|string',
        'time' => 'required|string',
        'color' => 'nullable|string',
    ]);

    // Tambahkan user_id ke data yang akan disimpan
    $validated['user_id'] = auth()->id();

    $info = InformasiUmum::create($validated);

    // Kembalikan seluruh data termasuk relasi user
    return InformasiUmum::with('user:id,nama,foto_profil')->orderBy('date', 'desc')->get();
}



  public function update(Request $request, $id)
{
    $info = InformasiUmum::findOrFail($id);

    $data = $request->validate([
        'title' => 'required|string|max:255', // ⬅️ tambahkan ini
        'text' => 'required|string',
    ]);

    $info->update($data);
    $info->refresh(); // ambil data terupdate dari database

    return response()->json($info);
}

    public function destroy($id)
{
    try {
        \Log::info("🗑️ Request hapus untuk ID: $id");

        $info = InformasiUmum::find($id);

        if (!$info) {
            \Log::warning("⚠️ Informasi dengan ID $id tidak ditemukan.");
            return response()->json(['message' => 'Data tidak ditemukan'], 404);
        }

        $info->delete();
        \Log::info("✅ Berhasil hapus informasi ID: $id");

        return response()->json(['message' => 'Deleted'], 200);
    } catch (\Exception $e) {
        \Log::error("❌ Gagal hapus informasi: " . $e->getMessage());
        return response()->json(['message' => 'Terjadi kesalahan server.'], 500);
    }
}


}
