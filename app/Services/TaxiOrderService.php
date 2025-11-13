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

public function unsetSentDate($validatedData)
{
    $query = Order::whereDate('visit_data', '>=', $validatedData['date_from'])
        ->whereDate('visit_data', '<=', $validatedData['date_to'])
        ->whereDoesntHave('currentStatus', function ($q) {
            $q->whereIn('status_order_id', [3, 4]);
        })
        ->where('taxi_id', $validatedData['taxi_id'])
        ->whereNull('deleted_at')
        ->whereNull('cancelled_at')
        ->whereNotNull('taxi_sent_at');

    $orders = $query->get();

    foreach ($orders as $order) {
        $order->taxi_sent_at = null;
        $order->otmena_taxi = 1;

        $cancelTaxiComment = 'Отмена передачи в такси: сведения об отмене переданы оператором ' .
            auth()->user()->name . ' (' . auth()->user()->litera . ')' .
            ' ' . now()->format('d.m.Y H:i');

        $order->komment = $order->komment ? $order->komment . "\n" . $cancelTaxiComment : $cancelTaxiComment;
        $order->save();
    }

    return $orders->count();
}

public function transferPredictiveData($validatedData)
{
    $query = Order::whereDate('visit_data', '>=', $validatedData['date_from'])
        ->whereDate('visit_data', '<=', $validatedData['date_to'])
        ->where('type_order', 1)
        ->whereHas('currentStatus', function ($q) {
            $q->where('status_order_id', 2);
        })
        ->where('taxi_id', $validatedData['taxi_id'])
        ->whereNotNull('predv_way')
        ->where('predv_way', '>', 0)
        ->whereNull('deleted_at')
        ->whereNull('cancelled_at');

    $orders = $query->get();
    $updatedCount = 0;

    foreach ($orders as $order) {
        try {
            $taxi = \App\Models\Taxi::find($validatedData['taxi_id']);
            $taxiWay = $order->predv_way;
            $taxiPrice = calculateTripPriceWithPickup($order, 11, $taxi);
            $taxiVozm = calculateReimbursementAmount($order, 11, $taxi);

            $order->taxi_way = $taxiWay;
            $order->taxi_price = $taxiPrice;
            $order->taxi_vozm = $taxiVozm;

            $comment = 'Перенос предварительных данных в фактические: ' .
                'дальность ' . number_format($taxiWay, 11, '.', '') . ' км, ' .
                'цена ' . number_format($taxiPrice, 11, '.', '') . ' руб., ' .
                'возмещение ' . number_format($taxiVozm, 11, '.', '') . ' руб. ' .
                'Оператор: ' . auth()->user()->name . ' (' . auth()->user()->litera . ') ' .
                'дата: ' . now()->format('d.m.Y H:i');

            $order->komment = $order->komment ? $order->komment . "\n" . $comment : $comment;
            $order->save();
            $updatedCount++;
        } catch (\Exception $e) {
            \Log::error('Ошибка при переносе данных для заказа ' . $order->id, [
                'exception' => $e,
                'order_id' => $order->id
            ]);
        }
    }

    return $updatedCount;
}
    
    
}