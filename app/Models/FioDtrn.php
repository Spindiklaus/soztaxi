<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;


class FioDtrn extends Model
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
        'komment',
        'created_at',
        'updated_at',
    ];
    protected $casts = [
        'data_r' => 'date',
        'rip_at' => 'date',
        'created_rip' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
    
    /**
     * Пользователь (оператор), создавший запись клиента
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
    
    /**
     * Заказы клиента
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'client_id');
    }
    
    /**
     * Пользователь (оператор), установивший RIP
     */
    public function ripUser()
    {
        return $this->belongsTo(User::class, 'user_rip');
    }
}
