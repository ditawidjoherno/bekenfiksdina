<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\absensi;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
{
    public function getUserData(Request $request)
{
    $user = Auth::user();

    if ($user) {
        return response()->json([
            'user' => [
                'id' => $user->id,
                'nama' => $user->nama,
                'role' => $user->role,
                'foto_profil' => $user->foto_profil, // ⬅️ pastikan ini eksplisit
                // tambahkan field lain jika perlu
            ]
        ], 200);
    }

    return response()->json([
        'error' => 'User not authenticated'
    ], 401);
}
    
    public function getUserCounts()
    {
        try {
            $jumlahSiswa = DB::table('users')->where('role', 'siswa')->count();
            $jumlahGuru = DB::table('users')->where('role', 'guru')->count();
            $jumlahTotal = DB::table('users')->count();
    
            return response()->json([
                'siswa' => $jumlahSiswa,
                'guru' => $jumlahGuru,
                'total' => $jumlahTotal,
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'error' => 'Gagal mengambil data',
                'message' => $e->getMessage(),
            ], 500);
        }
}

public function getAllSiswa()
{
    $siswa = User::where('role', 'siswa')->get()->map(function ($user) {
        return [
            'id' => $user->id,
            'nama' => $user->nama,
            'nisn' => $user->nisn,
            'jenis_kelamin' => $user->jenis_kelamin,
            'kelas' => $user->kelas,
            'foto_profil' => $user->foto_profil
                ? asset('storage/' . $user->foto_profil)
                : asset('images/profilsiswa.jpg'),
        ];
    });

    return response()->json([
        'status' => 'success',
        'data' => $siswa
    ], 200);
}

public function getAllGuru()
{
    $guru = User::where('role', 'guru')->get()->map(function ($user) {
        return [
            'id' => $user->id,
            'nama' => $user->nama,
            'nip' => $user->nip,
            'jenis_kelamin' => $user->jenis_kelamin,
            'kelas' => $user->kelas,
            'foto_profil' => $user->foto_profil
                ? asset('storage/' . $user->foto_profil)
                : asset('images/profilguru.jpg'), // Ganti dengan default foto guru
        ];
    });

    return response()->json([
        'status' => 'success',
        'data' => $guru
    ], 200);
}

    public function getUsersWithTotal()
{
    // Mengambil seluruh data user
    $users = User::all();

    // Mengambil jumlah total user
    $totalUsers = $users->count();

    // Proses menambahkan data NIP, NISN, dan foto profil sesuai kondisi
    $usersWithDetails = $users->map(function($user) {
        return [
            'id' => $user->id,
            'name' => $user->nama,
            'gender' => $user->jenis_kelamin,
            'class' => $user->kelas,
            'nip' => $user->nip ? $user->nip : null,
            'nisn' => $user->nisn ? $user->nisn : null,
            'foto_profil' => $user->foto_profil
                ? asset('storage/' . $user->foto_profil)
                : asset('images/profilsiswa.jpg'),  // fallback foto default jika foto kosong
        ];
    });

    // Mengembalikan response JSON dengan data user dan total count
    return response()->json([
        'status' => 'success',
        'total_users' => $totalUsers,
        'data' => $usersWithDetails,
    ], 200);
}


    public function getProfile(Request $request)
{
    $user = auth('api')->user();

    return response()->json([
        'status' => 'success',
        'data' => [
            'id'          => $user->id,
            'nama'        => $user->nama,
            'email'       => $user->email,
            'role'        => $user->role,
            'nip'         => $user->nip,
            'nisn'        => $user->nisn,
            'kelas'       => $user->kelas,
            'jenis_kelamin' => $user->jenis_kelamin,
            'nomor_hp'    => $user->nomor_hp,
            'agama'       => $user->agama,
            'foto_profil' => $user->foto_profil, // ✅ WAJIB ADA
        ]
    ]);
}

public function updateProfile(Request $request)
{ 
    $user = auth('api')->user(); 

    $request->validate([
        'nama' => 'nullable|string|max:255',
        'email' => 'nullable|email|unique:users,email,' . $user->id,
        'foto_profil' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        'nomor_hp' => 'nullable|string|max:20',
        'agama' => 'nullable|string|max:255',
    ]);

    if ($request->has('nama')) $user->nama = $request->nama;
    if ($request->has('email')) $user->email = $request->email;
    if ($request->has('nomor_hp')) $user->nomor_hp = $request->nomor_hp;
    if ($request->has('agama')) $user->agama = $request->agama;

    if ($request->hasFile('foto_profil')) {
    $image = $request->file('foto_profil');
    \Log::info('✅ File diterima:', ['name' => $image->getClientOriginalName()]);

    $path = $image->store('profiles', 'public');
    \Log::info('📦 Path disimpan:', ['path' => $path]);

    $user->foto_profil = $path; // ⬅️ WAJIB untuk menyimpan ke database
} else {

}

    $user->save();

// Tambahkan aktivitas
UserActivity::create([
    'user_id' => $user->id,
    'action' => 'update profile',
    'description' => 'User updated their profile information',
]);

return response()->json([
    'message' => 'Profil berhasil diperbarui',
    'data' => $user
]);


}

public function uploadFoto(Request $request)
{
    $user = auth('api')->user();
    \Log::info('📤 uploadFoto dipanggil', ['hasFile' => $request->hasFile('foto_profil')]);

    if ($request->hasFile('foto_profil')) {
        $image = $request->file('foto_profil');
        $path = $image->store('profiles', 'public');
        $user->foto_profil = $path;
        $user->save();

        return response()->json([
            'message' => 'Foto berhasil diperbarui',
            'data' => $user
        ]);
    }

    return response()->json(['message' => 'Tidak ada file dikirim'], 400);
}


    public function index(Request $request)
{
    $role = $request->query('role');
    if ($role) {
        return response()->json(User::where('role', $role)->get());
    }

    return response()->json(User::all());
}

public function updatePassword(Request $request)
{
    $user = auth('api')->user();

    $request->validate([
        'old_password' => 'required',
        'new_password' => 'required|min:8'
    ]);

    if (!Hash::check($request->old_password, $user->password)) {
        return response()->json(['message' => 'Password lama salah'], 400);
    }

    $user->password = Hash::make($request->new_password);
    $user->save();

        // ✅ Tambahan simpan password
    if ($request->has('password')) {
        $user->password = \Hash::make($request->password);
    }


    return response()->json(['message' => 'Password berhasil diperbarui']);
}
public function siswaGender()
    {
        $lakiLaki = User::where('role', 'siswa')->where('jenis_kelamin', 'L')->count();
        $perempuan = User::where('role', 'siswa')->where('jenis_kelamin', 'P')->count();

        return response()->json([
            'laki_laki' => $lakiLaki,
            'perempuan' => $perempuan,
        ]);
    }

    // Endpoint untuk guru
    public function guruGender()
    {
        $lakiLaki = User::where('role', 'guru')->where('jenis_kelamin', 'L')->count();
        $perempuan = User::where('role', 'guru')->where('jenis_kelamin', 'P')->count();

        return response()->json([
            'laki_laki' => $lakiLaki,
            'perempuan' => $perempuan,
        ]);
    }

   public function detailSiswa(Request $request)
{
    // Ambil nisn dari query string: ?nisn=...
    $nisn = $request->query('nisn');

    if (!$nisn) {
        return response()->json([
            'message' => 'NISN tidak diberikan.'
        ], 400);
    }

    $siswa = User::where('nisn', $nisn)->first();

    if (!$siswa) {
        return response()->json([
            'message' => 'Siswa tidak ditemukan.'
        ], 404);
    }

    // Hitung statistik dari tabel absensi berdasarkan user_id
    $hadir = Absensi::where('user_id', $siswa->id)->where('status', 'hadir')->count();
    $terlambat = Absensi::where('user_id', $siswa->id)->where('status', 'terlambat')->count();
    $tidakHadir = Absensi::where('user_id', $siswa->id)->where('status', 'tidak_hadir')->count();

    return response()->json([
        'nama' => $siswa->nama,
        'kelas' => $siswa->kelas,
        'nisn' => $siswa->nisn,
        'jenis_kelamin' => $siswa->jenis_kelamin,
        'agama' => $siswa->agama,
        'tanggal_lahir' => $siswa->tanggal_lahir,
        'nomor_hp' => $siswa->nomor_hp,
        'email' => $siswa->email,
        'foto' => $siswa->foto,
        'statistik' => [
            'hadir' => $hadir,
            'terlambat' => $terlambat,
            'tidak_hadir' => $tidakHadir,
        ]
    ]);
}

}
