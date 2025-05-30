<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
        protected $table = 'categories';
        protected $fillable = ['nmv', 'name', 'skidka', 'kol_p','is_soz','is_auto','is_gaz'];

}
