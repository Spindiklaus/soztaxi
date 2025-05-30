<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class OrderStatusHistory extends Model
{
     protected $fillable = ['order_id', 'status_order_id', 'user_id'];
     
     public function order()
    {
        return $this->belongsTo(Order::class);
    }
    
    public function status()
    {
        return $this->belongsTo(StatusOrder::class, 'status_order_id');
    }
    public function user()
    {
        return $this->belongsTo(\App\Models\User::class);
    }
}
