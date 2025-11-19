<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClientTripController;
use App\Http\Controllers\Admin\SocialTaxiOrderController;
use App\Http\Controllers\Api\OrderReportController;
// Добавляем новые контроллеры
use App\Http\Controllers\Api\HelperController;
use App\Http\Controllers\Api\CalculationController;

Route::get('/client-trips/{clientId}/{monthYear}', [ClientTripController::class, 'getClientTrips']);
Route::get('/client-actual-trips/{clientId}/{monthYear}', [ClientTripController::class, 'getClientActualTrips']);
Route::get('/client-taxi-sent-trips/{clientId}/{monthYear}', [ClientTripController::class, 'getClientTaxiSentTrips']);

Route::get('/social-taxi-orders/client-data/{clientId}', [SocialTaxiOrderController::class, 'getClientData'])
     ->name('api.social-taxi-orders.client-data');

// Группируем вспомогательные маршруты
Route::prefix('helpers')->group(function () {
    Route::get('/categories/{id}', [HelperController::class, 'getCategory'])
         ->name('api.categories.show');
    Route::get('/skidka-dops/{id}', [HelperController::class, 'getSkidkaDop'])
         ->name('api.skidka-dops.show');
    Route::get('/taxis/{id}', [HelperController::class, 'getTaxi'])
         ->name('api.taxis.show');
    Route::get('/client-last-trips/{clientId}', [HelperController::class, 'getClientLastTrips'])
         ->name('api.client-last-trips');
});

// Маршрут для расчетов
Route::post('/calculate-social-taxi-values', [CalculationController::class, 'calculateSocialTaxiValues'])
     ->name('api.calculate-social-taxi-values');

// Маршрут для получения заказов по фильтрам статуса из сводного отчета
Route::get('/orders-by-status-filter', [OrderReportController::class, 'getOrdersByStatusFilter']);