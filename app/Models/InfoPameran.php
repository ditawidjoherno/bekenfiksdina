<?php

// app/Models/StudyTourInfo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoPameran extends Model
{
    protected $table = 'info_pameran';

    protected $fillable = ['title', 'tanggal'];
}