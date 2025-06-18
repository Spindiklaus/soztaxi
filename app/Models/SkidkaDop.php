<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class SkidkaDop extends Model
{
    use HasFactory;
    
     protected $fillable = [
        'name',
        'skidka',
        'kol_p',
        'life',
        'komment',
    ];

    protected $casts = [
        'life' => 'boolean', // Преобразуем поле life в boolean
    ];
   
}
