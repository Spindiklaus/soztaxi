<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClientTripController;
use App\Http\Controllers\Operator\SocialTaxiOrderController;

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

