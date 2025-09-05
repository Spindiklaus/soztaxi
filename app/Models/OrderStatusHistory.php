<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
     protected $fillable = ['order_id', 'status_order_id', 'user_id'];
     
     /**
     * Заказ
     */
     public function order()
    {
        return $this->belongsTo(Order::class);
    }
    /**
     * Статус заказа
     */
    public function statusOrder()
    {
        return $this->belongsTo(StatusOrder::class, 'status_order_id');
    }
    
    /**
     * Пользователь (оператор)
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
