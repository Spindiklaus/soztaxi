<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class OrderGroup extends Model
{
    use HasFactory;

    protected $table = 'order_groups';

    // Уточните fillable поля, если планируете массовое заполнение
    protected $fillable = [
        'name',
    ];

    // Уточните casts, если необходимо
    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Заказы в этой группе
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'order_group_id');
    }
}