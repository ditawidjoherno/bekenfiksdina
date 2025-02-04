<?php

namespace App\Http\Controllers;

use App\Models\Nasabah;
use Illuminate\Http\Request;
use Tymon\JWTAuth\Facades\JWTAuth;

class NasabahController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api');
    }

    public function tambahNasabah(Request $request)
    {
        $validatedData = $request->validate([
            'nama' => 'required|string',
            'tipe_nasabah' => 'required|string',
            'nomor_telepon' => 'required|string',
            'alamat' => 'required|string',
            'jenis_kelamin' => 'required|string',
            'agama' => 'required|string',
            'tempat_lahir' => 'required|string',
            'tanggal_lahir' => 'required|date',
            'pekerjaan' => 'required|string',
            'alamat_pekerjaan' => 'required|string',
            'estimasi_penghasilan_bulanan' => 'required|string',
            'status_pernikahan' => 'required|string',
            'memiliki_anak' => 'required|boolean',
            'jumlah_anak' => 'required|integer',
            'data_pasangan' => 'nullable|array',
            'data_anak' => 'nullable|array',
        ]);

        $validatedData['created_by'] = auth()->user()->id;

        $nasabah = Nasabah::create($validatedData);

        if ($request->has('data_pasangan') && $validatedData['status_pernikahan'] === 'menikah') {
            $nasabah->pasangan()->create($request->data_pasangan);
        }

        if ($request->has('data_anak') && $validatedData['memiliki_anak']) {
            foreach ($request->data_anak as $anak) {
                $nasabah->anak()->create($anak);
            }
        }

        return response()->json(['message' => 'Data nasabah berhasil disimpan'], 201);
    }

    public function hapusNasabah(Request $request, $id)
    {
        try {
            // Validasi ID yang diterima
            $nasabah = Nasabah::find($id);
    
            if (!$nasabah) {
                return response()->json(['error' => 'Nasabah tidak ditemukan.'], 404);
            }
    
            // Hapus aktivitas
            $nasabah->delete();
    
            return response()->json(['success' => 'Nasabah berhasil dihapus.']);
        } catch (\Exception $e) {
            // Log error jika ada masalah saat menghapus
            Log::error('Error saat menghapus nasabah: ' . $e->getMessage());
            return response()->json(['error' => 'Terjadi kesalahan saat menghapus nasabah.'], 500);
        }
    }

    public function updateNasabah(Request $request, $id)
    {
        // Validasi data input, hanya validasi yang diperlukan
        $validatedData = $request->validate([
            'nama' => 'nullable|string',
            'tipe_nasabah' => 'nullable|string',
            'nomor_telepon' => 'nullable|string',
            'alamat' => 'nullable|string',
            'jenis_kelamin' => 'nullable|string',
            'agama' => 'nullable|string',
            'tempat_lahir' => 'nullable|string',
            'tanggal_lahir' => 'nullable|date',
            'pekerjaan' => 'nullable|string',
            'alamat_pekerjaan' => 'nullable|string',
            'estimasi_penghasilan_bulanan' => 'nullable|string',
            'status_pernikahan' => 'nullable|string',
            'memiliki_anak' => 'nullable|boolean',
            'jumlah_anak' => 'nullable|integer',
            'data_pasangan' => 'nullable|array',
            'data_anak' => 'nullable|array',
        ]);
    
        // Cari nasabah berdasarkan ID
        $nasabah = Nasabah::findOrFail($id);
    
        // Update hanya field yang ada di validatedData (yang dikirimkan oleh request)
        $nasabah->fill($validatedData);
        $nasabah->save();
    
        // Update atau tambah data pasangan jika ada
        if ($request->has('data_pasangan')) {
            if ($nasabah->pasangan) {
                $nasabah->pasangan()->update($request->data_pasangan); // Update pasangan jika sudah ada
            } else if ($validatedData['status_pernikahan'] === 'menikah' && $request->has('data_pasangan')) {
                $nasabah->pasangan()->create($request->data_pasangan); // Buat pasangan baru jika tidak ada
            }
        }
    
        // Update atau tambah data anak jika ada
        if ($request->has('data_anak') && $validatedData['memiliki_anak']) {
            $nasabah->anak()->delete(); // Hapus data anak lama dan menambahkan yang baru
            foreach ($request->data_anak as $anak) {
                $nasabah->anak()->create($anak); // Tambah data anak baru
            }
        }
    
        // Kirim response berhasil
        return response()->json(['message' => 'Data nasabah berhasil diperbarui'], 200);
    }
    
    public function index()
{
    $user = auth()->user();

    if ($user->jabatan === 'staff') {
        $nasabah = Nasabah::where('created_by', $user->id)  
                          ->with(['pasangan', 'anak'])
                          ->get();
        
        if ($nasabah->isEmpty()) {
            return response()->json([
                'message' => 'Tidak ada data nasabah yang dapat ditampilkan.'
            ], 404);
        }
    } else {
        $nasabah = Nasabah::with(['pasangan', 'anak'])->get();
    }

    $nasabahResponse = $nasabah->map(function ($nasabah) {
        return [
            'id' => $nasabah->id,
            'nama' => $nasabah->nama,
            'tipe_nasabah' => $nasabah->tipe_nasabah,
            'nomor_telepon' => $nasabah->nomor_telepon,
            'alamat' => $nasabah->alamat,
            'jenis_kelamin' => $nasabah->jenis_kelamin,
            'agama' => $nasabah->agama,
            'tempat_lahir' => $nasabah->tempat_lahir,
            'tanggal_lahir' => $nasabah->tanggal_lahir,
            'pekerjaan' => $nasabah->pekerjaan,
            'alamat_pekerjaan' => $nasabah->alamat_pekerjaan,
            'estimasi_penghasilan_bulanan' => $nasabah->estimasi_penghasilan_bulanan,
            'status_pernikahan' => $nasabah->status_pernikahan,
            'memiliki_anak' => $nasabah->memiliki_anak,
            'jumlah_anak' => $nasabah->jumlah_anak,
            'data_pasangan' => $nasabah->pasangan,
            'data_anak' => $nasabah->anak,
        ];
    });

    return response()->json([
        'data' => $nasabahResponse,
        'message' => 'Data nasabah berhasil ditemukan'
    ]);
}

public function getNamaNasabah(Request $request)
{
    $user = JWTAuth::user();

    if (in_array($user->role, ['admin', 'manager', 'unit head'])) {
        $nasabah = Nasabah::select('id', 'nama')->get();
    } else {
        $nasabah = Nasabah::select('id', 'nama')->where('created_by', $user->id)->get();
    }

    return response()->json([
        'message' => 'Data nama nasabah berhasil ditampilkan!',
        'data' => $nasabah
    ], 200);
}

public function profilNasabah($id)
{
    $nasabah = Nasabah::with(['pasangan', 'anak'])->find($id);

        if (!$nasabah) {
            return response()->json(['message' => 'Data not found'], 404);
        }

        $response = [
            'id' => $nasabah->id,
            'nama' => $nasabah->nama,
            'tipe_nasabah' => $nasabah->tipe_nasabah,
            'nomor_telepon' => $nasabah->nomor_telepon,
            'jenis_kelamin' => $nasabah->jenis_kelamin,
            'agama' => $nasabah->agama,
            'tempat_lahir' => $nasabah->tempat_lahir,
            'tanggal_lahir' => $nasabah->tanggal_lahir,
            'alamat' => $nasabah->alamat,
            'status_pernikahan' => $nasabah->status_pernikahan,
            'email' => $nasabah->email,
            'pekerjaan' => $nasabah->pekerjaan,
            'alamat_pekerjaan' => $nasabah->alamat_pekerjaan,
            'estimasi_penghasilan_bulanan' => $nasabah->estimasi_penghasilan_bulanan,
            'data_pasangan' => $nasabah->pasangan,
            'data_anak' => $nasabah->anak 
        ];

        return response()->json($response);
}

}
