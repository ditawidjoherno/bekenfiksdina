<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Aktivitas extends Model
{
    use HasFactory;

    protected $fillable = [
        'nasabah_id',
        'aktivitas',
        'tipe_nasabah',
        'prospek',
        'nominal_prospek',
        'closing',
        'status_aktivitas',
        'aktivitas_sales',
        'keterangan_aktivitas',
        'created_by',
    ];

    /**
     * Relasi dengan model User.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'created_by')->select(['id', 'nama']);
    }

    /**
     * Relasi dengan model Nasabah.
     */
    public function nasabah()
    {
        return $this->belongsTo(Nasabah::class, 'nasabah_id');
    }

    /**
     * Konversi atribut tanggal ke instance Carbon.
     */
    protected $dates = ['created_at', 'updated_at'];

// App\Models\Aktivitas.php
public function dokumentasi()
{
    return $this->hasMany(Dokumentasi::class, 'aktivitas_id');
}

}
