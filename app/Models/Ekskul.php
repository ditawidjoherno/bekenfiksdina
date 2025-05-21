<?php

// app/Models/Ekskul.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Ekskul extends Model
{
    protected $fillable = ['name', 'mentor', 'image'];
}
