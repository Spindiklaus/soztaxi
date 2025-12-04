<?php
// app/Services/TaxiService.php

namespace App\Services;

use App\Models\Order;
use App\Models\Taxi;
use Carbon\Carbon;

class TaxiService
{
    /**
     * Обновить цены в заказах по новым тарифам такси
     */
    public function updatePricesByTaxi(Taxi $taxi, $updateDate)
    {
        // Преобразуем дату в формат Carbon
        $updateDate = Carbon::createFromFormat('d.m.Y', $updateDate);

        // Находим заказы, удовлетворяющие условиям
        $orders = Order::where('taxi_id', $taxi->id)
            ->where(function($query) use ($updateDate) {
                // Для type_order 2 и 3: visit_data >= указанной даты
                $query->where(function($q) use ($updateDate) {
                    $q->whereIn('type_order', [2, 3])
                      ->whereDate('visit_data', '>=', $updateDate);
                })
                // Для type_order 1: visit_data >= указанной даты И taxi_sent_at не null
                ->orWhere(function($q) use ($updateDate) {
                    $q->where('type_order', 1)
                      ->whereDate('visit_data', '>=', $updateDate)
                      ->whereNotNull('taxi_sent_at')
                      ->where('taxi_way', '>=', 0);
                });
            })
            ->get();
            
        $ordersToUpdate = [];
        $ordersBefore = [];

        foreach ($orders as $order) {
            // Сохраняем текущие значения
            $ordersBefore[$order->id] = [
                'taxi_price' => $order->taxi_price,
                'taxi_vozm' => $order->taxi_vozm,
            ];

            // Рассчитываем новые значения в зависимости от типа заказа
            switch ($order->type_order) {
                case 2: // Легковое авто
                    if ($order->zena_type == 1) { // Одна сторона
                        $newPrice = $taxi->zena1_auto;
                    } else { // Обе стороны
                        $newPrice = $taxi->zena2_auto;
                    }
                    $order->taxi_price = $newPrice;
                    $order->taxi_vozm = $newPrice;
                    break;
                case 3: // ГАЗель
                    if ($order->zena_type == 1) { // Одна сторона
                        $newPrice = $taxi->zena1_gaz;
                    } else { // Обе стороны
                        $newPrice = $taxi->zena2_gaz;
                    }
                    $order->taxi_price = $newPrice;
                    $order->taxi_vozm = $newPrice;
                    break;
                case 1: // Соцтакси
                    if ($order->skidka_dop_all == 100) {
                        $newPrice = $taxi->koef * $order->taxi_way + $taxi->posadka;
                        $order->taxi_price = $newPrice;
                        $order->taxi_vozm = $newPrice;
                    } elseif ($order->skidka_dop_all == 50) {
                        $newPrice = $taxi->koef * $order->taxi_way + $taxi->posadka;
                        $newVozm = $taxi->koef50 * $order->taxi_way + $taxi->posadka50;
                        $order->taxi_price = $newPrice;
                        $order->taxi_vozm = $newVozm;
                    }
                    break;
            }
            
           // Проверяем, были ли изменения в ценах
            $wasChanged = ($order->taxi_price != $ordersBefore[$order->id]['taxi_price'] || 
                          $order->taxi_vozm != $ordersBefore[$order->id]['taxi_vozm']);

            if ($wasChanged) {
                // Добавляем комментарий об изменении тарифов
                $currentComment = $order->komment ?? '';
                $changeComment = 'Изменены тарифы ' . Carbon::now()->format('d.m.Y H:i') . 
                           ' (цена поездки: ' . $ordersBefore[$order->id]['taxi_price'] . ' → ' . $order->taxi_price . 
                           ', возм: ' . $ordersBefore[$order->id]['taxi_vozm'] . ' → ' . $order->taxi_vozm . ')';

                if ($currentComment) {
                    $order->komment = $currentComment . "\n" . $changeComment;
                } else {
                    $order->komment = $changeComment;
                }

                // Сохраняем информацию об изменении только для заказов, в которых произошли изменения
                $ordersToUpdate[$order->id] = [
                    'taxi_price' => $order->taxi_price,
                    'taxi_vozm' => $order->taxi_vozm,
                ];
            } else {
                // Если изменений не было, не добавляем заказ в отчет об изменениях
                unset($ordersBefore[$order->id]);
            }
        }

        // Обновляем заказы в базе данных
        foreach ($orders as $order) {
            $order->save();
        }
    
        return [
            'count' => count($ordersToUpdate),
            'orders_before' => $ordersBefore,
            'orders_after' => $ordersToUpdate,
        ];
    }
    
    public function getPriceUpdatePreview(Taxi $taxi, $updateDate)
{
    // Преобразуем дату в формат Carbon
    $updateDate = Carbon::createFromFormat('d.m.Y', $updateDate);

    // Находим заказы, удовлетворяющие условиям
    $orders = Order::where('taxi_id', $taxi->id)
        ->where(function($query) use ($updateDate) {
            // Для type_order 2 и 3: visit_data >= указанной даты
            $query->where(function($q) use ($updateDate) {
                $q->whereIn('type_order', [2, 3])
                  ->whereDate('visit_data', '>=', $updateDate);
            })
            // Для type_order 1: visit_data >= указанной даты И taxi_sent_at не null
            ->orWhere(function($q) use ($updateDate) {
                $q->where('type_order', 1)
                  ->whereDate('visit_data', '>=', $updateDate)
                  ->whereNotNull('taxi_sent_at')
                  ->where('taxi_way', '>=', 0);
            });
        })
        ->get();
        
    $ordersToUpdate = [];
    $ordersBefore = [];

    foreach ($orders as $order) {
        // Сохраняем текущие значения
        $ordersBefore[$order->id] = [
            'taxi_price' => $order->taxi_price,
            'taxi_vozm' => $order->taxi_vozm,
        ];

        // Рассчитываем новые значения в зависимости от типа заказа
        switch ($order->type_order) {
            case 2: // Легковое авто
                if ($order->zena_type == 1) { // Одна сторона
                    $newPrice = $taxi->zena1_auto;
                } else { // Обе стороны
                    $newPrice = $taxi->zena2_auto;
                }
                $order->taxi_price = $newPrice;
                $order->taxi_vozm = $newPrice;
                break;
            case 3: // ГАЗель
                if ($order->zena_type == 1) { // Одна сторона
                    $newPrice = $taxi->zena1_gaz;
                } else { // Обе стороны
                    $newPrice = $taxi->zena2_gaz;
                }
                $order->taxi_price = $newPrice;
                $order->taxi_vozm = $newPrice;
                break;
            case 1: // Соцтакси
                if ($order->skidka_dop_all == 100) {
                    $newPrice = $taxi->koef * $order->taxi_way + $taxi->posadka;
                    $order->taxi_price = $newPrice;
                    $order->taxi_vozm = $newPrice;
                } elseif ($order->skidka_dop_all == 50) {
                    $newPrice = $taxi->koef * $order->taxi_way + $taxi->posadka;
                    $newVozm = $taxi->koef50 * $order->taxi_way + $taxi->posadka50;
                    $order->taxi_price = $newPrice;
                    $order->taxi_vozm = $newVozm;
                }
                break;
        }
        
       // Проверяем, были ли изменения в ценах
        $wasChanged = ($order->taxi_price != $ordersBefore[$order->id]['taxi_price'] || 
                      $order->taxi_vozm != $ordersBefore[$order->id]['taxi_vozm']);

        if ($wasChanged) {
            // Сохраняем информацию об изменении только для заказов, в которых произошли изменения
            $ordersToUpdate[$order->id] = [
                'id' => $order->id,
                'pz_nom' => $order->pz_nom,
                'visit_data' => $order->visit_data,
                'type_order' => $order->type_order,
                'taxi_price_before' => $ordersBefore[$order->id]['taxi_price'],
                'taxi_vozm_before' => $ordersBefore[$order->id]['taxi_vozm'],
                'taxi_price_after' => $order->taxi_price,
                'taxi_vozm_after' => $order->taxi_vozm,
            ];
        }
    }

    return [
        'count' => count($ordersToUpdate),
        'orders' => $ordersToUpdate,
    ];
}
}