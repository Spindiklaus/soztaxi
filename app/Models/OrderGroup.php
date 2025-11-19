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
        'name', // Название группы
        'visit_date', // Общая дата поездки (самая ранняя из заказов)
        'taxi_way',
        'taxi_price',
        'taxi_vozm',
        'komment',  // Комментарий к группе
    ];

    // Уточните casts, если необходимо
    protected $casts = [
        'visit_date' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Заказы в этой группе
     */
    public function orders()
    {
        return $this->hasMany(Order::class, 'order_group_id')->orderBy('visit_data', 'asc');
    }
}