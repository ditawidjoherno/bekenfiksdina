<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'nama', 'role', 'nisn', 'nip', 'kelas', 'jenis_kelamin', 'agama', 'email', 'password', 'foto_profil',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
        'tanggal_lahir' => 'date',
    ];

    /**
     * Get the identifier that will be stored in the JWT subject claim.
     *
     * @return mixed
     */
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    /**
     * Return a key-value array, containing any custom claims to be added to the JWT.
     *
     * @return array
     */
    public function getJWTCustomClaims()
    {
        return [];
    }

    public function nasabah()
    {
        return $this->hasMany(Nasabah::class, 'created_by');
    }

    public function target()
    {
        return $this->hasMany(Target::class, 'staff_id');
    }
    
    public function aktivitas()
    {
        return $this->hasMany(Aktivitas::class);
    }
    public function targetTahunan()
    {
        return $this->hasMany(TargetTahunan::class);
    }

    public function ekskulDetail()
{
    return $this->hasMany(DetailEkskul::class, 'anggota_user_id');
}

}
