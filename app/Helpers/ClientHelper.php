<?php

use App\Models\Order;
use Carbon\Carbon;

if (!function_exists('getClientTripsCountInMonthByVisitDate')) {
    /**
     * Получить количество поездок клиента в месяц по дате поездки из заказа
     *
     * @param int $clientId ID клиента
     * @param Carbon|null $visitDate Дата поездки для определения месяца
     * @return int Количество поездок в месяце
     */
    function getClientTripsCountInMonthByVisitDate($clientId, $visitDate = null)
    {
        // Если дата поездки не указана, возвращаем 0
        if (!$visitDate || !$clientId) {
            return 0;
        }

        // Преобразуем дату в Carbon, если это строка
        if (is_string($visitDate)) {
            try {
                $visitDate = Carbon::parse($visitDate);
            } catch (\Exception $e) {
                return 0;
            }
        }

        // Определяем начало и конец месяца по дате поездки
        $startDate = $visitDate->copy()->startOfMonth();
        $endDate = $visitDate->copy()->endOfMonth();

        // Подсчитываем количество поездок клиента в этом месяце
        $tripCount = Order::where('client_id', $clientId)
            ->whereBetween('visit_data', [$startDate, $endDate])
            ->whereNotNull('visit_data') // Только с указанной датой поездки
            ->whereNull('deleted_at') // Только неудаленные заказы
            ->whereNull('cancelled_at') // Только неотмененные заказы
            ->count();

        return $tripCount;
    }
}