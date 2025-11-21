<?php

namespace App\Services;


use Illuminate\Http\Request;

class TaxiSentOrderBuilder extends SocialTaxiOrderBuilder
{
    /**
     * Применить фильтры для такси (переопределяем родительский метод)
     */
    public function applyFilters(Request $request): self
    {
        // Фильтрация по диапазону дат ПОЕЗДКИ
        $DateFrom = $request->input('date_from');
        $DateTo = $request->input('date_to');
        
        if ($DateFrom) {
            $this->query->whereDate('visit_data', '>=', $DateFrom);
        }
        if ($DateTo) {
            $this->query->whereDate('visit_data', '<=', $DateTo);
        }
        
        // Допускаем заказы со статусом 2
        $this->query->whereHas('currentStatus', function ($q) {
            $q->where('status_order_id', 2); // только переданные в такси
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
            // Специальная сортировка по ФИО клиента через JOIN
            $this->applyClientFioSorting($direction);
            break;
        default:
            $this->query->orderBy($sort, $direction);
            break;
    }
    
    return $this;
}
}