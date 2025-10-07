<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderReportController extends Controller
{
    public function getOrdersByStatusFilter(Request $request)
    {
        $visitDate = $request->input('visit_date');
        $typeOrderId = $request->input('type_order');
        $statusOrderId = $request->input('status_order');
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');

        // Проверка обязательного параметра status_order
        if ($statusOrderId === null) {
            return response()->json(['error' => 'Параметр status_order обязателен'], 400);
        }

        // Подзапрос: получить последний статус для каждого заказа в нужном диапазоне
        $latestStatusSubquery = DB::table('order_status_histories')
            ->selectRaw('order_id, status_order_id, created_at as status_created_at')
            ->whereRaw('created_at = (
                SELECT MAX(created_at)
                FROM order_status_histories osh2
                WHERE osh2.order_id = order_status_histories.order_id
                -- Добавляем фильтр по дате, если нужна только история в диапазоне
                -- и если $visitDate не задана (иначе фильтруем позже по visit_data)
                ' . ($visitDate ? '' : "AND (('$startDate' IS NULL OR osh2.created_at >= '$startDate') AND ('$endDate' IS NULL OR osh2.created_at <= '$endDate'))") . '
            )');

        $query = DB::table('orders')
            ->select(
                'orders.id',
                'orders.pz_nom',
                'orders.type_order',
                'orders.visit_data',
                'orders.visit_obratno',
                'orders.adres_otkuda',
                'orders.adres_kuda',
                'orders.adres_obratno as adres_obratno_addr', // Переименовываем, чтобы не пересекалось
                'fio_dtrns.fio as client_name',    
                DB::raw('JSON_OBJECT("status_order", JSON_OBJECT("id", so.id, "name", so.name, "color", so.color)) as current_status_json')
            )
            ->leftJoinSub($latestStatusSubquery, 'ls', 'orders.id', '=', 'ls.order_id')
            ->leftJoin('fio_dtrns', 'orders.client_id', '=', 'fio_dtrns.id') // 
            ->leftJoin('status_orders as so', 'ls.status_order_id', '=', 'so.id') // 
            ->where('ls.status_order_id', $statusOrderId) // Фильтр по последнему статусу
            ->whereNull('orders.deleted_at'); // Только неудалённые

        // Фильтр по дате поездки (visit_data)
        if ($visitDate) {
            $query->whereDate('orders.visit_data', $visitDate);
        } else {
            // Если дата не указана, используем диапазон из фильтра
            if ($startDate) {
                $query->whereDate('orders.visit_data', '>=', $startDate);
            }
            if ($endDate) {
                $query->whereDate('orders.visit_data', '<=', $endDate);
            }
        }

        // Фильтр по типу заказа
        if ($typeOrderId) {
            $query->where('orders.type_order', $typeOrderId);
        }
        
        // --- Добавляем сортировку ---
        $query->orderBy('orders.visit_data', 'asc');

        try {
            $orders = $query->get();
        } catch (\Exception $e) {
            \Log::error('Ошибка в getOrdersByStatusFilter: ' . $e->getMessage());
            \Log::error('Запрос: ' . $query->toSql());
            \Log::error('Параметры: ' . json_encode([$visitDate, $typeOrderId, $statusOrderId, $startDate, $endDate]));
            return response()->json(['error' => 'Внутренняя ошибка сервера'], 500);
        }

        // Преобразуем JSON строку в объект для current_status
        $orders = $orders->map(function ($order) {
            $order->current_status = json_decode($order->current_status_json);
            unset($order->current_status_json);
            // Создаем объект клиента с именем из fio_dtrns
            $order->client = ['name' => $order->client_name];
            unset($order->client_name);
            return $order;
        });

        $count = $orders->count();

        return response()->json([
            'orders' => $orders,
            'count' => $count
        ]);
    }
}