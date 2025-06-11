<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\Log;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;



class AuthController extends Controller
{
    public function register(Request $request)
{
    // Validasi input
    $request->validate([
        'nama' => 'required|string',
        'role' => 'required|in:siswa,guru',
        'nisn' => 'nullable|required_without:nip|unique:users',
        'nip' => 'nullable|required_without:nisn|unique:users',
        'kelas' => 'nullable',
        'jenis_kelamin' => 'nullable|in:L,P',
        'agama' => 'nullable',
        'email' => 'required|email|unique:users',
        'password' => 'required|string|min:8',
        'tanggal_lahir' => 'nullable|date',
        'nomor_hp' => 'nullable|string|max:15',
    ], [
        'nama.required' => 'Semua kolom wajib diisi.',
        'role.required' => 'Role wajib dipilih.',
        'role.in' => 'Role harus berupa siswa atau guru.',
        'nisn.required_without' => 'NISN harus diisi jika NIP kosong.',
        'nip.required_without' => 'NIP harus diisi jika NISN kosong.',
        'nisn.unique' => 'NISN sudah terdaftar.',
        'nip.unique' => 'NIP sudah terdaftar.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah digunakan.',
        'password.required' => 'Password wajib diisi.',
        'password.min' => 'Password minimal terdiri dari 8 karakter.',
    ]);

    // Simpan user (siswa atau guru)
    $user = User::create([
        'nama' => $request->nama,
        'role' => $request->role,
        'nisn' => $request->nisn,
        'nip' => $request->nip,
        'kelas' => $request->kelas,
        'jenis_kelamin' => $request->jenis_kelamin,
        'agama' => $request->agama,
        'email' => $request->email,
        'password' => Hash::make($request->password),
        'tanggal_lahir' => $request->tanggal_lahir,
        'nomor_hp' => $request->nomor_hp,
    ]);

    // ✅ Tambahkan otomatis akun orangtua jika role = siswa
    if ($request->role === 'siswa') {
        // Cek apakah sudah ada akun orangtua agar tidak duplikat
        $ortuNisn = 'OT_' . $request->nisn;
        if (!User::where('nisn', $ortuNisn)->exists()) {
            User::create([
                'nama' => 'Orangtua ' . $request->nama,
                'role' => 'orangtua',
                'nisn' => $ortuNisn,
                'anak_nisn' => $request->nisn,
                'email' => 'ortu.' . $request->email, // pastikan tidak duplicate
                'password' => bcrypt($request->tanggal_lahir), // pakai tgl lahir siswa
            ]);
        }
    }

    // Format tanggal lahir (untuk respons frontend)
    $formattedUser = $user->toArray();
    if ($user->tanggal_lahir) {
        $formattedUser['tanggal_lahir'] = \Carbon\Carbon::parse($user->tanggal_lahir)->format('d-m-Y');
    }

    return response()->json([
        'message' => 'User berhasil ditambahkan',
        'user' => $formattedUser
    ], 201);
}


public function index()
    {
        $users = User::all();
        return response()->json($users);
    }
    
public function login(Request $request)
{
    try {
        $request->validate([
    'identifier' => 'required|string',
    'password' => 'required|string',
], [
    'identifier.required' => 'NIP, NISN, atau email harus diisi.',
    'password.required' => 'Password tidak boleh kosong.',
]);


        $user = User::where('nisn', $request->identifier)
                    ->orWhere('nip', $request->identifier)
                    ->orWhere('email', $request->identifier)
                    ->first();

        if (!$user) {
            return response()->json(['message' => 'User tidak ditemukan!'], 401);
        }

        if (!Hash::check($request->password, $user->password)) {
            return response()->json(['message' => 'Password salah!'], 401);
        }

        $token = JWTAuth::fromUser($user);

        // ✅ Letakkan log aktivitas di sini:
        \App\Models\UserActivity::create([
            'user_id' => $user->id,
            'action' => 'Login',
            'description' => 'User berhasil login.',
        ]);

        return response()->json([
            'message' => 'Login berhasil!',
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60
        ]);
        
    } catch (\Throwable $e) {
        \Log::error('Login Error: ' . $e->getMessage());
        return response()->json([
            'message' => 'Terjadi kesalahan saat login.',
            'error' => $e->getMessage(),
        ], 500);
    }
}


    // Logout method (opsional)
    public function logout()
    {
        JWTAuth::invalidate(JWTAuth::getToken());
        
        UserActivity::create([
    'user_id' => auth()->id(),
    'action' => 'Logout',
    'description' => 'User berhasil logout.',
]);


        return response()->json(['message' => 'Successfully logged out']);
    }

    public function profile(Request $request)
    {
        $user = JWTAuth::user(); // Mendapatkan data user yang sedang login

        return response()->json(['user' => $user]);
    }
    
}
