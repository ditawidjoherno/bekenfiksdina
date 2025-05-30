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

    $data = $request->validate([
    'date' => 'required|date',
    'title' => 'required|string|max:255',
    'text' => 'required|string',
    'author' => 'nullable|string',
    'photo' => 'nullable|string', // ✅ tambahkan ini
    'time' => 'required|string',
    'color' => 'nullable|string',
]);


    $info = InformasiUmum::create($data);
    return response()->json($info, 201);
}


    public function update(Request $request, $id)
    {
        $info = InformasiUmum::findOrFail($id);

        $data = $request->validate([
            'text' => 'required|string',
        ]);

        $info->update($data);
        return response()->json($info);
    }

    public function destroy($id)
    {
        $info = InformasiUmum::findOrFail($id);
        $info->delete();
        return response()->json(['message' => 'Deleted']);
    }
}
