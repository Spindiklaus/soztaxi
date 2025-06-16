<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class FioRip extends Model
{
     use HasFactory;
     protected $table = 'fio_rips';
     protected $fillable = [
        'kl_id',
        'fio',
        'data_r',
        'sex',
        'adres',
        'rip_at',
        'nom_zap',
        'user_id',
        'komment',
    ];
     
    protected $casts = [
        'data_r' => 'date',
        'rip_at' => 'date',
    ]; 
    
    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
