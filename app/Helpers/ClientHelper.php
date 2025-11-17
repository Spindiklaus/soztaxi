<?php
// app/Helpers/ClientHelper.php

use App\Models\Order;
use Carbon\Carbon;

if (!function_exists('getClientTripsCountInMonthByVisitDate')) {
    /**
     * Получить количество поездок клиента в месяц по дате поездки из заказа
     *
     * @param int $clientId ID клиента
     * @param Carbon|null $visitDate Дата поездки для определения месяца
     * @param int|null $excludeOrderId ID заказа для исключения из подсчета
     * @return int Количество поездок в месяце
     */
    function getClientTripsCountInMonthByVisitDate($clientId, $visitDate = null, $excludeOrderId = null)
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
            ->whereNull('cancelled_at'); // Только неотмененные заказы
        // Если указан ID заказа для исключения, добавляем условие
        if ($excludeOrderId) {
            $tripCount->where('id', '!=', $excludeOrderId);
        }
//        dd($tripCount);
            

        return $tripCount->count();
    }
}

if (!function_exists('getClientActualTripsCountInMonthByVisitDate')) {
    /**
     * Получить количество фактических поездок клиента в месяц по дате поездки из заказа
     * Фактические поездки - это заказы с установленной датой закрытия (closed_at)
     *
     * @param int $clientId ID клиента
     * @param Carbon|null $visitDate Дата поездки для определения месяца
     * @param int|null $excludeOrderId ID заказа для исключения из подсчета
     * @return int Количество фактических поездок в месяце
     */
    function getClientActualTripsCountInMonthByVisitDate($clientId, $visitDate = null, $excludeOrderId = null)
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

        // Подсчитываем количество фактических поездок клиента в этом месяце
        // Фактические поездки - заказы с датой закрытия
        $query = Order::where('client_id', $clientId)
            ->whereBetween('visit_data', [$startDate, $endDate])
            ->whereNotNull('visit_data') // Только с указанной датой поездки
            ->whereNotNull('closed_at') // Только с датой закрытия (фактические поездки)
            ->whereNull('deleted_at') // Только неудаленные заказы
            ->whereNull('cancelled_at'); // Только неотмененные заказы

        // Если указан ID заказа для исключения, добавляем условие
        if ($excludeOrderId) {
            $query->where('id', '!=', $excludeOrderId);
        }

        return $query->count();
    }
}

if (!function_exists('getClientTaxiSentTripsCountInMonthByVisitDate')) {
    /**
     * Получить количество поездок клиента, переданных в такси, в месяц по дате поездки из заказа
     * Переданные в такси - это неотмененные заказы с ненулевым полем taxi_sent_at
     *
     * @param int $clientId ID клиента
     * @param Carbon|null $visitDate Дата поездки для определения месяца
     * @param int|null $excludeOrderId ID заказа для исключения из подсчета
     * @return int Количество поездок, переданных в такси, в месяце
     */
    function getClientTaxiSentTripsCountInMonthByVisitDate($clientId, $visitDate = null, $excludeOrderId = null)
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

        // Подсчитываем количество поездок, переданных в такси, клиента в этом месяце
        // Переданные в такси - неотмененные заказы с ненулевым полем taxi_sent_at
        $query = Order::where('client_id', $clientId)
            ->whereBetween('visit_data', [$startDate, $endDate])
            ->whereNotNull('visit_data') // Только с указанной датой поездки
            ->whereNotNull('taxi_sent_at') // Только переданные в такси
            ->whereNull('closed_at') // Только неудаленные заказы
            ->whereNull('deleted_at') // Только неудаленные заказы
            ->whereNull('cancelled_at'); // Только неотмененные заказы

        // Если указан ID заказа для исключения, добавляем условие
        if ($excludeOrderId) {
            $query->where('id', '!=', $excludeOrderId);
        }

        return $query->count();
    }
}