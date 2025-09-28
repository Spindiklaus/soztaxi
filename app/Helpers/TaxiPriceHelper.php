<?php

if (!function_exists('calculateSocialTaxiValues')) {
    /**
     * Рассчитать все значения для соцтакси
     *
     * @param float $predvWay Предварительная дальность поездки
     * @param mixed $taxi Оператор такси (модель или массив данных)
     * @param int $discount Скидка клиента (0, 50, 100)
     * @param int $kol_znak Количество знаков после запятой для округления
     * @return array Массив с рассчитанными значениями
     */
    function calculateSocialTaxiValues($predvWay, $taxi, $discount = 0, $kol_znak = 2)
    {
        // Проверка входных данных
        if (!$predvWay || $predvWay <= 0 || !$taxi) {
            return [
                'full_trip_price' => 0,
                'reimbursement_amount' => 0,
                'client_payment_amount' => 0
            ];
        }

        // Расчет полной цены поездки без посадки
        $fullTripPrice = 0;
        if ($discount == 100) {
            $fullTripPrice = ($taxi->koef ?? 0) * $predvWay;
        } elseif ($discount == 50) {
            $fullTripPrice = ($taxi->koef50 ?? 0) * $predvWay;
        }

        // Расчет суммы к возмещению
        $reimbursementAmount = 0;
        if ($discount == 100) {
            $reimbursementAmount = ($taxi->koef ?? 0) * $predvWay + ($taxi->posadka ?? 0);
        } elseif ($discount == 50) {
            $reimbursementAmount = ($taxi->koef50 ?? 0) * $predvWay + ($taxi->posadka50 ?? 0);
        }

        // Расчет суммы к оплате клиентом
        $fullReimbursement = ($taxi->koef ?? 0) * $predvWay + ($taxi->posadka ?? 0);
        $clientPaymentAmount = $fullReimbursement - $reimbursementAmount;

        return [
            'full_trip_price' => round($fullTripPrice, $kol_znak),
            'reimbursement_amount' => round($reimbursementAmount, $kol_znak),
            'client_payment_amount' => round($clientPaymentAmount, $kol_znak)
        ];
    }
}

// Обновите существующие функции, чтобы они использовали общую логику:
if (!function_exists('calculateFullTripPrice')) {
    // Предварительная цена поездки полная, без учета посадки, руб.
    function calculateFullTripPrice($order, $kol_znak=2, $taxi = null)
    {
        if (!$order || empty($order->predv_way) || !$taxi) {
            return 0;
        }

        $discount = $order->skidka_dop_all ?? 0;
        $values = calculateSocialTaxiValues($order->predv_way, $taxi, $discount, $kol_znak);
        return $values['full_trip_price'];
    }
}

if (!function_exists('calculateReimbursementAmount')) {
    // Сумма к возмещению, руб.
    function calculateReimbursementAmount($order, $kol_znak=2, $taxi = null)
    {
        if (!$order || empty($order->predv_way) || !$taxi) {
            return 0;
        }

        $discount = $order->skidka_dop_all ?? 0;
        $values = calculateSocialTaxiValues($order->predv_way, $taxi, $discount, $kol_znak);
        return $values['reimbursement_amount'];
    }
}

if (!function_exists('calculateClientPaymentAmount')) {
    // Предварительная сумма к оплате клиентом, руб.
    function calculateClientPaymentAmount($order, $kol_znak = 2, $taxi = null)
    {
        if (!$order || empty($order->predv_way) || !$taxi) {
            return 0;
        }

        $discount = $order->skidka_dop_all ?? 0;
        $values = calculateSocialTaxiValues($order->predv_way, $taxi, $discount, $kol_znak);
        return $values['client_payment_amount'];
    }
}