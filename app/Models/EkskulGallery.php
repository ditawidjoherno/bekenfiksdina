<?php


namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EkskulGallery extends Model
{
    use HasFactory;

    protected $fillable = [
        'ekskul_id',
        'image_path',
        'description',
        'uploaded_at'
    ];

    public function ekskul()
    {
        return $this->belongsTo(Ekskul::class);
    }
}
