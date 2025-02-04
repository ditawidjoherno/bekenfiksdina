<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TargetMingguan extends Model
{
    use HasFactory;

    protected $table = 'target_mingguan';

    protected $fillable = ['nip', 'target'];

    public function user()
    {
        return $this->belongsTo(User::class, 'nip', 'nip');
    }
}