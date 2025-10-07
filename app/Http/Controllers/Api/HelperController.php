<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\SkidkaDop;
use App\Models\Taxi;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class HelperController extends Controller
{
    /**
     * Получение данных категории по ID
     */
    public function getCategory($id): JsonResponse
    {
        $category = Category::find($id);
        if (!$category) {
            return response()->json(['error' => 'Категория не найдена'], 404);
        }
        return response()->json([
            'id' => $category->id,
            'nmv' => $category->nmv,
            'name' => $category->name,
            'skidka' => $category->skidka,
            'kol_p' => $category->kol_p,
        ]);
    }

    /**
     * Получение данных дополнительных условий по ID
     */
    public function getSkidkaDop($id): JsonResponse
    {
        $dopus = SkidkaDop::find($id);
        if (!$dopus) {
            return response()->json(['error' => 'Дополнительные условия не найдены'], 404);
        }
        return response()->json([
            'id' => $dopus->id,
            'name' => $dopus->name,
            'skidka' => $dopus->skidka,
            'kol_p' => $dopus->kol_p,
        ]);
    }

    /**
     * Получение данных такси по ID
     */
    public function getTaxi($id): JsonResponse
    {
        $taxi = Taxi::find($id);
        if (!$taxi) {
            return response()->json(['error' => 'Такси не найдено'], 404);
        }
        return response()->json([
            'id' => $taxi->id,
            'name' => $taxi->name,
            'zena1_auto' => $taxi->zena1_auto,
            'zena2_auto' => $taxi->zena2_auto,
            'zena1_gaz' => $taxi->zena1_gaz,
            'zena2_gaz' => $taxi->zena2_gaz,
        ]);
    }

    /**
     * Получение последних 10 поездок клиента
     */
    public function getClientLastTrips($clientId): JsonResponse
    {
        try {
            $trips = Order::where('client_id', $clientId)
                ->whereNull('deleted_at')
                ->orderBy('visit_data', 'desc')
                ->select([
                    'adres_otkuda',
                    'adres_kuda',
                    'adres_obratno',
                    'predv_way'
                ])
                ->limit(10)
                ->get();

            return response()->json($trips);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Ошибка получения данных поездок'], 500);
        }
    }
}