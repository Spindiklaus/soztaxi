<!-- resources/views/operator-orders/calendar-components/trip-counts.blade.php -->

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-2">

    <div id="trip-counts-content" class="p-1 "> 
        <!-- Количество поездок клиента -->
        <div class="bg-gray-50 p-1 rounded-lg">
            <div class="flex items-center">
                <span class="text-lg font-semibold text-gray-800">Общее число заказов:</span>
                <button 
                    onclick="showClientTrips({{ $client->id }}, '{{ $startDate->format('Y-m') }}')"
                    class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                    {{ getClientTripsCountInMonthByVisitDate($client->id, $startDate) }}
                </button>
                 , в т.ч. со 100% скидкой: 
                    <span class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                        &nbsp;{{ getClientFreeTripsCountInMonthByVisitDate($client->id,$startDate) }}
                    </span>
                <span class="text-lg font-semibold text-gray-800">&nbsp;Передано в такси:</span>
                <button 
                    onclick="showClientTaxiSentTrips({{ $client->id }}, '{{ $startDate->format('Y-m') }}')"
                    class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800 hover:bg-yellow-200 transition-colors">
                    {{ getClientTaxiSentTripsCountInMonthByVisitDate($client->id, $startDate) }}
                </button>
                <span class="text-lg font-semibold text-gray-800">&nbsp;Закрыто:</span>
                <button 
                    onclick="showClientActualTrips({{ $client->id }}, '{{ $startDate->format('Y-m') }}')"
                    class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800 hover:bg-green-200 transition-colors">
                    {{ getClientActualTripsCountInMonthByVisitDate($client->id, $startDate) }}
                </button>
            </div>
        </div>
    </div>
</div>