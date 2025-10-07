<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Taxi;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CalculationController extends Controller
{
    /**
     * Расчет значений поездки для соцтакси
     */
    public function calculateSocialTaxiValues(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'predv_way' => 'required|numeric|min:0',
                'taxi_id' => 'required|exists:taxis,id',
                'skidka_dop_all' => 'nullable|integer|in:0,50,100',
            ]);

            $predvWay = $validated['predv_way'];
            $taxiId = $validated['taxi_id'];
            $discount = $validated['skidka_dop_all'] ?? 0;

            $taxi = Taxi::find($taxiId);
            if (!$taxi) {
                return response()->json(['error' => 'Оператор такси не найден'], 404);
            }

            // Используем общую функцию для расчета (предполагается, что она доступна)
            // Замените 'calculateSocialTaxiValues' на имя вашей реальной функции
            $values = calculateSocialTaxiValues($predvWay, $taxi, $discount, 2);

            return response()->json([
                'success' => true,
                'full_trip_price' => $values['full_trip_price'],
                'reimbursement_amount' => $values['reimbursement_amount'],
                'client_payment_amount' => $values['client_payment_amount'],
                'taxi_name' => $taxi->name,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}