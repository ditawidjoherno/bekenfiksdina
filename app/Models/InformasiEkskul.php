<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InformasiEkskul extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'description', 'author', 'time', 'color', 'ekskul_id'];

    public function ekskul()
{
    return $this->belongsTo(\App\Models\Ekskul::class);
}

}
