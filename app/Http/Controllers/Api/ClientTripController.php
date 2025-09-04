<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FioDtrn;
use App\Models\Order;
use Carbon\Carbon;

class ClientTripController extends Controller {

    public function getClientTrips($clientId, $monthYear)
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
        
        // Получаем поездки клиента за месяц
        $trips = Order::with(['currentStatus.statusOrder']) // Загружаем текущий статус
            ->where('client_id', $clientId)
            ->whereBetween('visit_data', [$startDate, $endDate])
            ->whereNotNull('visit_data')
            ->whereNull('deleted_at')
            ->whereNull('cancelled_at')
            ->where('otmena_taxi', 0)
            ->select([
                'id',
                'visit_data',
                'adres_otkuda',
                'adres_kuda', 
                'type_order',
                'pz_nom',
                'client_id',
                'taxi_id',
                'taxi_sent_at',
                'closed_at'
            ])
            ->orderBy('visit_data')
            ->get();
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

public function getClientActualTrips($clientId, $monthYear)
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
        
        // Получаем фактические поездки клиента за месяц (с датой закрытия)
        $trips = Order::with(['currentStatus.statusOrder']) // Загружаем текущий статус
            ->where('client_id', $clientId)
            ->whereBetween('visit_data', [$startDate, $endDate])
            ->whereNotNull('visit_data')
            ->whereNotNull('closed_at') // Только фактические поездки
            ->whereNull('deleted_at')
            ->whereNull('cancelled_at')
            ->where('otmena_taxi', 0)
            ->select([
                'id',
                'visit_data',
                'adres_otkuda',
                'adres_kuda', 
                'type_order',
                'pz_nom',
                'client_id',
                'taxi_id',
                'taxi_sent_at',
                'closed_at'
            ])
            ->orderBy('visit_data')
            ->get();
        
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

public function getClientTaxiSentTrips($clientId, $monthYear)
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
        
        // Получаем поездки, переданные в такси, клиента за месяц
        $trips = Order::with(['currentStatus.statusOrder']) // Загружаем текущий статус
            ->where('client_id', $clientId)
            ->whereBetween('visit_data', [$startDate, $endDate])
            ->whereNotNull('visit_data')
            ->whereNotNull('taxi_sent_at') // Только переданные в такси
            ->whereNull('deleted_at')
            ->whereNull('cancelled_at')
            ->where('otmena_taxi', 0)
            ->select([
                'id',
                'visit_data',
                'adres_otkuda',
                'adres_kuda', 
                'type_order',
                'pz_nom',
                'client_id',
                'taxi_id',
                'taxi_sent_at',
                'closed_at'
            ])
            ->orderBy('visit_data')
            ->get();
        
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
