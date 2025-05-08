<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\User;

class AktivitasKegiatan extends Model
{
    use HasFactory;
    

    protected $table = 'aktivitas_kegiatan';

    protected $fillable = [
        'nama_kegiatan',
        'kategori',
        'start',
        'end',
        'tipe',
        'foto',
        'total_days_left',
        'participants',
        'penanggung_jawab_id',
    ];

    protected $casts = [
        'participants' => 'array',
        'start' => 'datetime',
        'end' => 'datetime',
    ];

    // Relasi ke penanggung jawab (Guru)
    public function penanggungJawab()
    {
        return $this->belongsTo(User::class, 'penanggung_jawab_id');
    }

    // Relasi ke peserta (Siswa)
    public function peserta()
    {
        return User::whereIn('id', $this->participants ?? [])->get();
    }
}
