<?php

namespace App\Services;

use Illuminate\Http\Request;

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
    public function applySorting(Request $request): self
{
    $sort = $request->get('sort', 'visit_data');
    $direction = $request->get('direction', 'asc');
    
    // Убедитесь, что правильно применяется сортировка
    switch ($sort) {
        case 'visit_data':
            $this->query->orderBy('visit_data', $direction);
            break;
        case 'pz_data':
            $this->query->orderBy('pz_data', $direction);
            break;
        case 'client_fio':
            $this->query->orderBy('client_fio', $direction);
            break;
        default:
            $this->query->orderBy($sort, $direction);
            break;
    }
    
    return $this;
}
}