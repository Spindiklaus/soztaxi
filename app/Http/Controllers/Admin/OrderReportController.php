<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;


class OrderReportController extends BaseController
{
    public function index(Request $request)
    {
        // Устанавливаем start_date и end_date по умолчанию
        $startDate = $request->input('start_date', Carbon::now()->startOfMonth()->toDateString());
        $endDate = $request->input('end_date', Carbon::now()->toDateString());

        // Подзапрос для получения последнего статуса по каждому заказу
                $latestStatusSubquery = DB::table('order_status_histories')
            ->selectRaw('order_id, status_order_id')
            ->whereRaw('created_at = (
                SELECT MAX(created_at)
                FROM order_status_histories osh2
                WHERE osh2.order_id = order_status_histories.order_id
            )');
        // Основной запрос: агрегация по дате (без времени)
        $report = DB::table('orders')
            ->select(
                DB::raw('DATE(visit_data) as visit_date'), // ✅ Используем DATE(visit_data)
                DB::raw('SUM(CASE WHEN ls.status_order_id = 1 THEN 1 ELSE 0 END) AS status_1_count'),
                DB::raw('SUM(CASE WHEN ls.status_order_id = 2 THEN 1 ELSE 0 END) AS status_2_count'),
                DB::raw('SUM(CASE WHEN ls.status_order_id = 3 THEN 1 ELSE 0 END) AS status_3_count'),
                DB::raw('SUM(CASE WHEN ls.status_order_id = 4 THEN 1 ELSE 0 END) AS status_4_count')
            )
            ->leftJoinSub($latestStatusSubquery, 'ls', 'orders.id', '=', 'ls.order_id')
            ->whereNull('orders.deleted_at') // ✅ Только неудалённые заказы    
            ->when($startDate, function ($q, $startDate) {
                return $q->whereDate('visit_data', '>=', $startDate);
            })
            ->when($endDate, function ($q, $endDate) {
                return $q->whereDate('visit_data', '<=', $endDate);
            })
            ->whereNotNull('visit_data')
            ->groupBy(DB::raw('DATE(visit_data)')) // ✅ Группируем по DATE(visit_data)
            ->orderBy('visit_date', 'asc') // ✅ Сортируем по alias
            ->get();


        return view('reports.orders_visit', compact('report', 'startDate', 'endDate'));
    }
}