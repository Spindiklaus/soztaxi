<?php

if (!function_exists('calculateFullTripPrice')) {
    /**
     * Рассчитать предварительную полную цену поездки без учета посадки
     *
     * @param mixed $order Заказ (модель или массив данных)
     * @param int $kol_znak Количество знаков после запятой для округления
     * @param mixed $taxi Оператор такси (модель или массив данных)
     * @return float Цена поездки
     */
    function calculateFullTripPrice($order, $kol_znak=2, $taxi = null)
    {
        // Если заказ не передан или нет дальности поездки
        if (!$order || empty($order->predv_way)) {
            return 0;
        }

        // Если оператор такси не передан, пытаемся получить его из заказа
        if (!$taxi && isset($order->taxi)) {
            $taxi = $order->taxi;
        }

        // Если нет оператора такси
        if (!$taxi) {
            return 0;
        }

        // Получаем скидку клиента
        $discount = $order->skidka_dop_all ?? 0;

        // Рассчитываем цену в зависимости от скидки
        $price = 0;
        if ($discount == 100) {
            // Полная скидка - используем коэффициент koef
            $price = ($taxi->koef ?? 0) * ($order->predv_way ?? 0);
        } elseif ($discount == 50) {
            // 50% скидка - используем коэффициент koef50
            $price = ($taxi->koef50 ?? 0) * ($order->predv_way ?? 0);
        } else {
            // Без скидки или другая скидка - используем koef
            $price = 0;
        }

        // Округляем
        return round($price, $kol_znak);
    }
}

if (!function_exists('calculateReimbursementAmount')) {
    /**
     * Рассчитать предварительную сумму к возмещению
     *
     * @param mixed $order Заказ (модель или массив данных)
     * @param int $kol_znak Количество знаков после запятой для округления 
     * @param mixed $taxi Оператор такси (модель или массив данных)
     * @return float Сумма к возмещению
     */
    function calculateReimbursementAmount($order, $kol_znak=2, $taxi = null)
    {
        // Если заказ не передан или нет дальности поездки
        if (!$order || empty($order->predv_way)) {
            return 0;
        }

        // Если оператор такси не передан, пытаемся получить его из заказа
        if (!$taxi && isset($order->taxi)) {
            $taxi = $order->taxi;
        }

        // Если нет оператора такси
        if (!$taxi) {
            return 0;
        }

        // Получаем скидку клиента
        $discount = $order->skidka_dop_all ?? 0;

        // Рассчитываем цену в зависимости от скидки
        $price = 0;
        if ($discount == 100) {
            // Полная скидка - используем коэффициент koef
            $price = ($taxi->koef ?? 0) * ($order->predv_way ?? 0) + $taxi->posadka;
        } elseif ($discount == 50) {
            // 50% скидка - используем коэффициент koef50
            $price = ($taxi->koef50 ?? 0) * ($order->predv_way ?? 0) + $taxi->posadka50;
        } else {
            // Без скидки или другая скидка - используем koef
            $price = 0;
        }

        // Округляем
        return round($price, $kol_znak);
    }
}

if (!function_exists('calculateClientPaymentAmount')) {
    /**
     * Рассчитать сумму к оплате клиентом
     * Формула: сумма к возмещению при 100% скидке - сумма к возмещению текущего заказа
     *
     * @param mixed $order Заказ (модель или массив данных)
     * @param int $kol_znak Количество знаков после запятой для округления
     * @param mixed $taxi Оператор такси (модель или массив данных)
     * @return float Сумма к оплате
     */
    function calculateClientPaymentAmount($order, $kol_znak = 2, $taxi = null)
    {
        // Если заказ не передан или нет дальности поездки
        if (!$order || empty($order->predv_way)) {
            return 0;
        }

        // Если оператор такси не передан, пытаемся получить его из заказа
        if (!$taxi && isset($order->taxi)) {
            $taxi = $order->taxi;
        }

        // Если нет оператора такси
        if (!$taxi) {
            return 0;
        }

        // Рассчитываем сумму к возмещению при 100% скидке
        $fullReimbursement = 0;
        $fullReimbursement = ($taxi->koef ?? 0) * ($order->predv_way ?? 0) + ($taxi->posadka ?? 0);
        $fullReimbursement = round($fullReimbursement, $kol_znak);

        // Рассчитываем сумму к возмещению текущего заказа
        $currentReimbursement = calculateReimbursementAmount($order, $kol_znak, $taxi);

        // Сумма к оплате = разница между полной суммой и текущей суммой
        $paymentAmount = $fullReimbursement - $currentReimbursement;

        // Округляем
        return round($paymentAmount, $kol_znak);
    }
}
