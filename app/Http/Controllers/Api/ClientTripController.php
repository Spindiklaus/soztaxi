<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FioDtrn;
use App\Models\Order;
use Carbon\Carbon;

class ClientTripController extends Controller {

    public function getClientTrips($clientId, $monthYear)
    {
        return $this->getTrips($clientId, $monthYear, 'all');
    }

    public function getClientActualTrips($clientId, $monthYear)
    {
        return $this->getTrips($clientId, $monthYear, 'actual');
    }

    public function getClientTaxiSentTrips($clientId, $monthYear)
    {
        return $this->getTrips($clientId, $monthYear, 'taxi-sent');
    }

    /**
     * Получить поездки клиента с различными фильтрами
     *
     * @param int $clientId ID клиента
     * @param string $monthYear Месяц и год в формате Y-m
     * @param string $type Тип фильтрации: 'all', 'actual', 'taxi-sent'
     * @return \Illuminate\Http\JsonResponse
     */
    private function getTrips($clientId, $monthYear, $type = 'all')
    {
        try {
            // Получаем клиента
            $client = FioDtrn::find($clientId);
            if (!$client) {
                return response()->json(['error' => 'Клиент не найден'], 404);
            }
            
            // Парсим дату
            $date = Carbon::createFromFormat('Y-m', $monthYear);
            if (!$date) {
                $date = Carbon::now();
            }
            
            // Определяем начало и конец месяца
            $startDate = $date->copy()->startOfMonth();
            $endDate = $date->copy()->endOfMonth();
            
            // Создаем запрос
            $query = Order::with(['currentStatus.statusOrder'])
                ->where('client_id', $clientId)
                ->whereBetween('visit_data', [$startDate, $endDate])
                ->whereNotNull('visit_data')
                ->whereNull('deleted_at')
                ->whereNull('cancelled_at')
                ->select([
                    'id',
                    'visit_data',
                    'visit_obratno',
                    'adres_otkuda',
                    'adres_kuda', 
                    'adres_obratno',
                    'type_order',
                    'pz_nom',
                    'client_id',
                    'taxi_id',
                    'taxi_sent_at',
                    'closed_at'
                ])
                ->orderBy('visit_data');
            
            // Применяем дополнительные фильтры в зависимости от типа
            switch ($type) {
                case 'actual':
                    // Только фактические поездки (с датой закрытия)
                    $query->whereNotNull('closed_at');
                    break;
                case 'taxi-sent':
                    // Только переданные в такси (с датой передачи в такси)
                    $query->whereNotNull('taxi_sent_at');
                    break;
                case 'all':
                default:
                    // Все поездки (без дополнительных фильтров)
                    break;
            }
            
            // Получаем поездки
            $trips = $query->get();
            
            // Форматируем название периода на русском
            $monthNames = [
                'январь', 'февраль', 'март', 'апрель', 'май', 'июнь',
                'июль', 'август', 'сентябрь', 'октябрь', 'ноябрь', 'декабрь'
            ];
            $monthName = $monthNames[$date->month - 1];
            $period = $monthName . ' ' . $date->year;
            
            return response()->json([
                'trips' => $trips,
                'clientName' => $client->fio,
                'count' => $trips->count(),
                'period' => $period
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка загрузки данных: ' . $e->getMessage()], 500);
        }
    }
}