<?php

// app/Models/StudyTourInfo.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class InfoStudyTour extends Model
{
    protected $table = 'study_tour_info';

    protected $fillable = ['title', 'tanggal'];
}