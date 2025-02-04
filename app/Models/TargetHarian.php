<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetHarian extends Model
{
    use HasFactory;

    protected $table = 'target_harian';

    protected $fillable = ['nip', 'target'];

    public function user()
    {
        return $this->belongsTo(User::class, 'nip', 'nip');
    }
}
