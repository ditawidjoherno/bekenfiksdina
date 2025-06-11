<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TourGallery extends Model
{
    protected $fillable = ['study_tour_id', 'image_path'];

    public function studyTour()
    {
        return $this->belongsTo(StudyTour::class);
    }
}

