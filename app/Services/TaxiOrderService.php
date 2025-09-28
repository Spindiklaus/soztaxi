<?php

namespace App\Services;

class TaxiOrderService extends SocialTaxiOrderService
{
    /**
     * Получить параметры URL для такси
     */
    public function getUrlParams() {
        
        $params = request()->only([
            'sort', 'direction', 'visit_date_from', 'visit_date_to', 'page', 'taxi_id'
        ]);
        \Log::info('GetUrlParams result', ['params' => $params]);
        return $params;
    }
}