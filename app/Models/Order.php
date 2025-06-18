<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Order extends Model
{
    use HasFactory;
    use SoftDeletes; // Включаем поддержку мягкого удаления
    protected $table = 'orders';
    protected $fillable = [
        'type_order',
        'client_id',
        'client_tel',
        'client_invalid',
        'client_sopr',
        'category_id',
        'category_skidka',
        'category_limit',
        'dopus_id',
        'skidka_dop_all',
        'kol_p_limit',
        'pz_nom',
        'pz_data',
        'adres_otkuda',
        'adres_kuda',
        'adres_obratno',
        'zena_type',
        'visit_data',
        'predv_way',
        'taxi_id',
        'taxi_sent_at',
        'taxi_price',
        'taxi_way',
        'cancelled_at',
        'otmena_taxi',
        'closed_at',
        'komment',
        'user_id',
    ];
    protected $dates = ['deleted_at']; // Указываем, что deleted_at — это дата
    protected $casts = [
        'pz_data' => 'datetime',
        'visit_data' => 'datetime',
        'taxi_sent_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'closed_at' => 'datetime',
        'otmena_taxi' => 'boolean',
    ];
    
    public function statusHistory()
    {
        return $this->hasMany(OrderStatusHistory::class);
    }

    public function currentStatus()
    {
        return $this->hasOne(OrderStatusHistory::class)->latestOfMany();
    }
    
}
