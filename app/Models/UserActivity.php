<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class UserActivity extends Model
{
    // Izinkan mass-assignment untuk kolom berikut
    protected $fillable = ['user_id', 'action', 'description'];

    // Aktifkan timestamps untuk created_at dan updated_at
    public $timestamps = true;

    // Relasi ke user
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
