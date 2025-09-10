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
