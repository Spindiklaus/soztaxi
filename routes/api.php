<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClientTripController;
use App\Http\Controllers\Admin\SocialTaxiOrderController;

Route::get('/client-trips/{clientId}/{monthYear}', [ClientTripController::class, 'getClientTrips']);
Route::get('/client-actual-trips/{clientId}/{monthYear}', [ClientTripController::class, 'getClientActualTrips']);
Route::get('/client-taxi-sent-trips/{clientId}/{monthYear}', [ClientTripController::class, 'getClientTaxiSentTrips']);
// Новый маршрут для получения данных клиента по AJAX
Route::get('/social-taxi-orders/client-data/{clientId}', [SocialTaxiOrderController::class, 'getClientData'])
     ->name('api.social-taxi-orders.client-data');

// Получение данных категории по ID
Route::get('/categories/{id}', function($id) {
    $category = \App\Models\Category::find($id);
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
})->name('api.categories.show');

// Получение данных дополнительных условий по ID
Route::get('/skidka-dops/{id}', function($id) {
    $dopus = \App\Models\SkidkaDop::find($id);
    if (!$dopus) {
        return response()->json(['error' => 'Дополнительные условия не найдены'], 404);
    }
    return response()->json([
        'id' => $dopus->id,
        'name' => $dopus->name,
        'skidka' => $dopus->skidka,
        'kol_p' => $dopus->kol_p,
    ]);
})->name('api.skidka-dops.show');

// Расчет значений поездки для соцтакси
Route::post('/calculate-social-taxi-values', function(\Illuminate\Http\Request $request) {
    try {
        $validated = $request->validate([
            'predv_way' => 'required|numeric|min:0',
            'taxi_id' => 'required|exists:taxis,id',
            'skidka_dop_all' => 'nullable|integer|in:0,50,100',
        ]);

        $predvWay = $validated['predv_way'];
        $taxiId = $validated['taxi_id'];
        $discount = $validated['skidka_dop_all'] ?? 0;

        $taxi = \App\Models\Taxi::find($taxiId);
        if (!$taxi) {
            return response()->json(['error' => 'Оператор такси не найден'], 404);
        }

        // Используем общую функцию для расчета
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
})->name('api.calculate-social-taxi-values');

Route::get('/taxis/{id}', function($id) {
    $taxi = \App\Models\Taxi::find($id);
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
})->name('api.taxis.show');

// Получение последних 10 поездок клиента
Route::get('/client-last-trips/{clientId}', function($clientId) {
    try {
        $trips = \App\Models\Order::where('client_id', $clientId)
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
})->name('api.client-last-trips');



