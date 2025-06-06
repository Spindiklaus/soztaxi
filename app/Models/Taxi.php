<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Taxi extends Model
{
    use HasFactory;
    protected $fillable = ['name', 'life', 'user_id', 'koef', 'posadka', 'koef50', 'posadka50', 'zena1_auto', 'zena2_auto', 'zena1_gaz', 'zena2_gaz', 'komment'];
    
     protected $casts = [
        'created_at' => 'datetime:d.m.Y H:i:s',
        'updated_at' => 'datetime:d.m.Y H:i:s',
        ];

    public function user()
       {
           return $this->belongsTo(User::class);
       } 
}
