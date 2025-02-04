<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    public function getUser(Request $request)
    {
        $user = Auth::user();

        return response()->json([
            'data' => [
                'id' => $user->id,
                'nip' => $user->nip,
                'nama' => $user->nama,
                'alamat' => $user->alamat,
                'email' => $user->email,
                'foto_profil' => $user->foto_profil,
                'jabatan' => $user->jabatan,
                'jenis_kelamin' => $user->jenis_kelamin,
                'nomor_hp' => $user->nomor_hp,
                'tanggal_lahir' => $user->tanggal_lahir ? $user->tanggal_lahir->format('Y-m-d') : null,
                'tempat_lahir' => $user->tempat_lahir,
                'created_at' => $user->created_at ? $user->created_at->format('Y-m-d') : null,
                'updated_at' => $user->updated_at ? $user->updated_at->format('Y-m-d') : null,
            ],
            'message' => 'Login successful'
        ]);
    }
}
