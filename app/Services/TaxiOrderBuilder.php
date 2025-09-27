<?php

namespace App\Services;

use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;

class TaxiOrderBuilder extends SocialTaxiOrderBuilder
{
    /**
     * Применить фильтры для такси (переопределяем родительский метод)
     */
    public function applyFilters(Request $request): self
    {
        // Фильтрация по диапазону дат ПОЕЗДКИ
        $visitDateFrom = $request->input('visit_date_from');
        $visitDateTo = $request->input('visit_date_to');
        
        if ($visitDateFrom) {
            $this->query->whereDate('visit_data', '>=', $visitDateFrom);
        }
        if ($visitDateTo) {
            $this->query->whereDate('visit_data', '<=', $visitDateTo);
        }
        
        // Исключаем отмененные заказы
        $this->query->whereDoesntHave('currentStatus', function ($q) {
            $q->where('status_order_id', 3); // 3 = Отменён
        });
        
        return $this;
    }
}