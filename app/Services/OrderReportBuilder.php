<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class OrderReportBuilder
{
    protected $startDate;
    protected $endDate;

    public function __construct($startDate, $endDate)
    {
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function build()
    {
        // Подзапрос: получить последний статус для каждого заказа
        $latestStatusSubquery = DB::table('order_status_histories')
            ->selectRaw('order_id, status_order_id')
            ->whereRaw('created_at = (
                SELECT MAX(created_at)
                FROM order_status_histories osh2
                WHERE osh2.order_id = order_status_histories.order_id
            )');

        $rows = DB::table('orders')
            ->select(
                DB::raw('DATE(visit_data) as visit_date'),
                'type_order',
                DB::raw('SUM(CASE WHEN ls.status_order_id = 1 THEN 1 ELSE 0 END) AS status_1_count'),
                DB::raw('SUM(CASE WHEN ls.status_order_id = 2 THEN 1 ELSE 0 END) AS status_2_count'),
                DB::raw('SUM(CASE WHEN ls.status_order_id = 3 THEN 1 ELSE 0 END) AS status_3_count'),
                DB::raw('SUM(CASE WHEN ls.status_order_id = 4 THEN 1 ELSE 0 END) AS status_4_count')
            )
            ->leftJoinSub($latestStatusSubquery, 'ls', 'orders.id', '=', 'ls.order_id')
            ->when($this->startDate, function ($q) {
                return $q->whereDate('visit_data', '>=', $this->startDate);
            })
            ->when($this->endDate, function ($q) {
                return $q->whereDate('visit_data', '<=', $this->endDate);
            })
            ->whereNotNull('visit_data')
            ->whereNull('orders.deleted_at') // Только неудалённые
            ->groupBy(DB::raw('DATE(visit_data)'), 'type_order')
            ->orderBy('visit_date', 'asc')
            ->orderBy('type_order', 'asc')
            ->get();

        // Группируем результаты по дате
        $grouped = [];
        foreach ($rows as $row) {
            $date = $row->visit_date;
            if (!isset($grouped[$date])) {
                $grouped[$date] = [
                    'visit_date' => $date,
                    'types' => [],
                ];
            }

            $grouped[$date]['types'][$row->type_order] = [
                'status_1_count' => $row->status_1_count,
                'status_2_count' => $row->status_2_count,
                'status_3_count' => $row->status_3_count,
                'status_4_count' => $row->status_4_count,
            ];
        }

        return collect($grouped);
    }
}