<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\ClientTripController;

Route::get('/client-trips/{clientId}/{monthYear}', [ClientTripController::class, 'getClientTrips']);
Route::get('/client-actual-trips/{clientId}/{monthYear}', [ClientTripController::class, 'getClientActualTrips']);
Route::get('/client-taxi-sent-trips/{clientId}/{monthYear}', [ClientTripController::class, 'getClientTaxiSentTrips']);