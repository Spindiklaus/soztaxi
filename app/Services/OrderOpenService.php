<?php
// app/Services/OrderOpenService.php

namespace App\Services;

// Наследуемся от общего сервиса, если он предоставляет базовую логику
// Если нет, можно просто использовать Service или даже без родителя
class OrderOpenService extends SocialTaxiOrderService // Если SocialTaxiOrderService предоставляет getUrlParams, можно наследоваться
{
    /**
     * Получить параметры URL для открытия заказов
     */
    public function getUrlParams() {
        $params = request()->only([
            'sort', 'direction', 'visit_date_from', 'visit_date_to', 'page', 'taxi_id'
        ]);
        // \Log::info('GetUrlParams OrderOpenService result', ['params' => $params]);
        return $params;
    }
}
