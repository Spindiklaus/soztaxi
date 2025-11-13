<?php
// app/Services/OrderCloseService.php


namespace App\Services;

class OrderCloseService extends SocialTaxiOrderService
{
    /**
     * Получить параметры URL для такси
     */
    public function getUrlParams() {
        
        $params = request()->only([
            'sort', 'direction', 'date_from', 'date_to', 'page', 'taxi_id'
        ]);
        \Log::info('GetUrlParams OrderCloseService result', ['params' => $params]);
        return $params;
    }
   
}