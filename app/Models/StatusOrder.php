<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class StatusOrder extends Model
{
    protected $table = 'status_orders';
    protected $fillable = ['name','color'];
    
    /**
     * История статусов с этим статусом
     */
    public function orderStatusHistories()
    {
        return $this->hasMany(OrderStatusHistory::class, 'status_order_id');
    }
    
    public function orders()
    {
        return $this->hasMany(Order::class);
    }
}
