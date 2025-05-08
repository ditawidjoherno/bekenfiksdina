<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;

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
        ]);

        // Simpan user
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
        ]);

        return response()->json([
            'message' => 'User berhasil ditambahkan',
            'user' => $user
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
            ]);
    
            $user = User::where('nisn', $request->identifier)
                        ->orWhere('nip', $request->identifier)
                        ->first();
    
            if (!$user) {
                return response()->json(['message' => 'User tidak ditemukan!'], 401);
            }
    
            if (!Hash::check($request->password, $user->password)) {
                return response()->json(['message' => 'Password salah!'], 401);
            }
    
            // Coba generate token, tapi tangani jika ada error
            try {
                $token = $user->createToken('auth_token')->plainTextToken;
            } catch (\Exception $e) {
                Log::error('Token creation failed: ' . $e->getMessage());
                return response()->json(['message' => 'Gagal membuat token.'], 500);
            }
    
            return response()->json([
                'message' => 'Login berhasil!',
                'user' => $user,
                'token' => $token,
            ]);
    
        } catch (\Throwable $e) {
            Log::error('Login Error: ' . $e->getMessage());
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

        return response()->json(['message' => 'Successfully logged out']);
    }

    public function profile(Request $request)
    {
        $user = JWTAuth::user(); // Mendapatkan data user yang sedang login

        return response()->json(['user' => $user]);
    }
    
}
