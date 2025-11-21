<?php
// app/Services/TaxiOrderService.php


namespace App\Services;
use App\Models\Order;


class TaxiOrderService extends SocialTaxiOrderService
{
    /**
     * Получить параметры URL для такси
     */
    public function getUrlParams() {
        
        $params = request()->only([
            'sort', 'direction', 'date_from', 'date_to', 'page', 'taxi_id'
        ]);
        \Log::info('GetUrlParams result', ['params' => $params]);
        return $params;
    }
    
    //передача в такси
    public function setSentDate($validatedData, $taxiSentAt)
{
    $query = Order::whereDate('visit_data', '>=', $validatedData['date_from'])
        ->whereDate('visit_data', '<=', $validatedData['date_to'])
        ->whereDoesntHave('currentStatus', function ($q) {
            $q->whereIn('status_order_id', [3, 4]);
        })
        ->where('taxi_id', $validatedData['taxi_id'])
        ->whereNull('deleted_at')
        ->whereNull('cancelled_at')
        ->whereNull('taxi_sent_at');

    $orders = $query->get();

    foreach ($orders as $order) {
        $order->taxi_sent_at = $taxiSentAt;
        
        // Добавляем комментарий о передаче в такси
        $sendTaxiComment = 'Передача в такси: сведения переданы оператором ' .
            auth()->user()->name . ' (' . auth()->user()->litera . ')' .
            ' ' . now()->format('d.m.Y H:i');

        if ($order->komment) {
            $order->komment = $order->komment . "\n" . $sendTaxiComment;
        } else {
            $order->komment = $sendTaxiComment;
        }
        
        
        $order->save();
    }

    return $orders->count();
}
  
    
}