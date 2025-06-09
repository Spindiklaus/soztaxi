<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class Fio_dtrn extends Model
{
    use HasFactory;

    protected $table = 'fio_dtrns';

    protected $fillable = [
        'kl_id',
        'fio',
        'data_r',
        'sex',
        'rip_at',
        'created_rip',
        'user_rip',
        'user_id',
        'komment'
    ];
    protected $casts = [
        'data_r' => 'date',
        'rip_at' => 'datetime',
        'created_rip' => 'datetime',
    ];
    
}
