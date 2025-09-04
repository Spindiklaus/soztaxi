<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\FioDtrn;
use App\Models\Order;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ClientTripController extends Controller
{
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
            $trips = Order::where('client_id', $clientId)
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
                    'client_id'
                ])
                ->orderBy('visit_data')
                ->get();
            
            return response()->json([
                'trips' => $trips,
                'clientName' => $client->fio,
                'count' => $trips->count(),
                'period' => $startDate->format('F Y')
            ]);
            
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка загрузки данных'], 500);
        }
    }
}