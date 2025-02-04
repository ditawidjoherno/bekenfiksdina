<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $credentials = $request->only('nip', 'password');

        try {
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        return response()->json([
            'data' => [
                'token' => $token,
            ],
            'message' => 'Login successful'
        ]);
    }

    public function logout()
    {
        auth()->logout();
        return response()->json(['message' => 'Successfully logged out']);
    }
  
    public function addUser(Request $request)
{
    // Validasi data yang dikirimkan
    $validated = $request->validate([
        'nip' => 'required|unique:users,nip',
        'password' => 'required',
        'nama' => 'required',
        'alamat' => 'required',
        'email' => 'required|email|unique:users,email',
        'jabatan' => 'required|in:admin,staff,manager,unit head',
        'jenis_kelamin' => 'required|in:Laki-laki,Perempuan',
        'nomor_hp' => 'required',
        'tanggal_lahir' => 'required|date',
        'tempat_lahir' => 'required',
    ], [
        'nip.required' => 'NIP harus diisi.',
        'nip.unique' => 'NIP sudah terdaftar.',
        'password.required' => 'Password harus diisi.',
        'nama.required' => 'Nama harus diisi.',
        'alamat.required' => 'Alamat harus diisi.',
        'email.required' => 'Email harus diisi.',
        'email.email' => 'Email yang dimasukkan tidak valid.',
        'email.unique' => 'Email sudah terdaftar.',
        'jabatan.required' => 'Jabatan harus diisi.',
        'jabatan.in' => 'Jabatan harus salah satu dari admin, staff, manager, atau unit head.',
        'jenis_kelamin.required' => 'Jenis kelamin harus diisi.',
        'jenis_kelamin.in' => 'Jenis kelamin harus salah satu dari Laki-laki atau Perempuan.',
        'nomor_hp.required' => 'Nomor HP harus diisi.',
        'tanggal_lahir.required' => 'Tanggal lahir harus diisi.',
        'tanggal_lahir.date' => 'Tanggal lahir harus berupa tanggal yang valid.',
        'tempat_lahir.required' => 'Tempat lahir harus diisi.',
    ]);

    // Jika validasi berhasil, lanjutkan untuk membuat user baru
    try {
        $user = User::create([
            'nip' => $request->nip,
            'password' => Hash::make($request->password),
            'nama' => $request->nama,
            'alamat' => $request->alamat,
            'email' => $request->email,
            'jabatan' => $request->jabatan,
            'jenis_kelamin' => $request->jenis_kelamin,
            'nomor_hp' => $request->nomor_hp,
            'tanggal_lahir' => $request->tanggal_lahir,
            'tempat_lahir' => $request->tempat_lahir,
        ]);

        return response()->json(['message' => 'User berhasil dibuat', 'user' => $user], 201);
    } catch (\Exception $e) {
        return response()->json([
            'message' => 'Terjadi kesalahan saat membuat user: ' . $e->getMessage()
        ], 500);
    }
}

    public function updateProfile(Request $request)
    {
        $user = auth()->user();
    
        $request->validate([
            'nama' => 'sometimes|string|max:255',
            'alamat' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . $user->id,
            'nomor_hp' => 'sometimes|string|max:15',
            'tanggal_lahir' => 'sometimes|date',
            'tempat_lahir' => 'sometimes|string|max:255',
        ]);
    
        $user->update($request->only([
            'nama',
            'alamat',
            'email',
            'nomor_hp',
            'tanggal_lahir',
            'tempat_lahir'
        ]));
    
        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user
        ], 200);
    }
    

    public function changePassword(Request $request)
    {
        $request->validate([
            'new_password' => 'required|string|min:6|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json(['message' => 'Password changed successfully'], 200);
    }

    public function updateProfileImage(Request $request)
    {
        $request->validate([
            'foto_profil' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);
    
        $user = auth()->user();
    
        if ($request->hasFile('foto_profil')) {
            // Hapus gambar lama jika ada
            if ($user->foto_profil && Storage::exists(str_replace('/storage/', '', $user->foto_profil))) {
                Storage::delete(str_replace('/storage/', '', $user->foto_profil));
            }
    
            // Simpan gambar baru
            $imagePath = $request->file('foto_profil')->store('profile_images', 'public');
            $user->foto_profil = Storage::url($imagePath);
            $user->save();
    
            return response()->json([
                'message' => 'Foto Profil berhasil diperbarui.',
                'foto_profil' => $user->foto_profil,
            ], 200);
        }
    
        return response()->json(['error' => 'No image file found'], 400);
    }

    public function getStaffData()
    {
        try {
            $staffData = User::where('jabatan', 'staff')
                ->select('id as user_id', 'nip', 'nama', 'foto_profil')  
                ->get();
    
            $staffData->transform(function ($staff) {
                $staff->foto_profil = $staff->foto_profil 
                    ? url($staff->foto_profil) 
                    : null;
                return $staff;
            });
    
            return response()->json([
                'success' => true,
                'data' => $staffData
            ], 200);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal mengambil data staff',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    public function namaStaffNip(Request $request)
    {
        $nip = $request->query('nip');

        $user = user::where('nip', $nip)->first();

        if ($user) {
            return response()->json([
                'nip' => $user->nip,
                'nama' => $user->nama,
                'jabatan' => $user->jabatan,
                'foto_profil' => $user->foto_profil,
            ]);
        } else {
            return response()->json([
                'message' => 'Staff not found'
            ], 404);
        }
    }
}
