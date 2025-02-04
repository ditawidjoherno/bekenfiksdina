<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nasabah extends Model
{
    use HasFactory;

    protected $fillable = [
        'nama', 'tipe_nasabah', 'nomor_telepon', 'alamat', 'jenis_kelamin', 
        'agama', 'tempat_lahir', 'tanggal_lahir', 'pekerjaan', 
        'alamat_pekerjaan', 'estimasi_penghasilan_bulanan', 'key_person', 
        'status_pernikahan', 'memiliki_anak', 'jumlah_anak', 'created_by'
    ];

    protected $table = 'nasabah';

    public function staff()
    {
        return $this->belongsTo(User::class, 'created_by');
    }    

    public function pasangan()
    {
        return $this->hasOne(PasanganNasabah::class);
    }

    public function anak()
    {
        return $this->hasMany(AnakNasabah::class);
    }

    public function aktivitas()
    {
        return $this->hasMany(Aktivitas::class);
    }
}
