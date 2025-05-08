<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Storage;

class UserController extends Controller
{
    public function getUserData(Request $request)
    {
        // Get the authenticated user
        $user = Auth::user();

        if ($user) {
            return response()->json([
                'user' => $user
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
        // Jika menggunakan field 'role' langsung di tabel users
        $siswa = User::where('role', 'siswa')->get();

        // Jika menggunakan Spatie Laravel Permission
        // $siswa = User::role('siswa')->get();

        return response()->json([
            'status' => 'success',
            'data' => $siswa
        ], 200);
    }
public function getAllGuru()
    {
        // Jika menggunakan field 'role' langsung di tabel users
        $guru = User::where('role', 'guru')->get();

        // Jika menggunakan Spatie Laravel Permission
        // $siswa = User::role('siswa')->get();

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
    
        // Proses menambahkan data NIP dan NISN sesuai kondisi
        $usersWithDetails = $users->map(function($user) {
            return [
                'id' => $user->id,
                'name' => $user->nama,
                'gender' => $user->jenis_kelamin,
                'class' => $user->kelas,
                'nip' => $user->nip ? $user->nip : null, // Jika ada NIP, tampilkan, jika tidak tampilkan null
                'nisn' => $user->nisn ? $user->nisn : null, // Jika ada NISN, tampilkan, jika tidak tampilkan null
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
        $user = $request->user(); // Ambil user yang sedang login

        return response()->json([
            'status' => 'success',
            'data' => $user,
        ], 200);
    }

    public function updateProfile(Request $request)
    {
        $user = auth()->user(); // Ambil data user yang sedang login

        // Tentukan validasi yang berbeda untuk setiap field
        $validator = Validator::make($request->all(), [
            'nama' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'foto_profil' => 'sometimes|image|mimes:jpeg,png,jpg,gif|max:2048',
            'nomor_hp' => 'sometimes|string|max:15',
            'agama' => 'sometimes|string|max:255',
            'jenis_kelamin' => 'sometimes|in:L,P',
        ]);

        // Jika validasi gagal
        if ($validator->fails()) {
            return response()->json(['error' => $validator->errors()], 400);
        }

        // Update hanya field yang diberikan dalam request
        if ($request->has('nama')) {
            $user->nama = $request->nama;
        }

        if ($request->has('email')) {
            $user->email = $request->email;
        }

        if ($request->has('foto_profil')) {
            $image = $request->file('foto_profil');
            $path = $image->store('profiles', 'public');
            $user->foto_profil = $path;
        }

        if ($request->has('nomor_hp')) {
            $user->nomor_hp = $request->nomor_hp;
        }

        if ($request->has('agama')) {
            $user->agama = $request->agama;
        }

        if ($request->has('jenis_kelamin')) {
            $user->jenis_kelamin = $request->jenis_kelamin;
        }

        // Simpan perubahan
        $user->save();

        return response()->json([
            'message' => 'Profil berhasil diperbarui',
            'data' => $user
        ], 200);
    }

}
