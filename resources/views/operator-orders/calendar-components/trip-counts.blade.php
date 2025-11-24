<!-- resources/views/operator-orders/calendar-components/trip-counts.blade.php -->

<div class="bg-white shadow overflow-hidden sm:rounded-lg mb-2">
    <div class="bg-gray-50 px-4 py-2 rounded-t-lg">
        <button type="button"
                onclick="toggleTripCounts()"
                class="flex items-center justify-between w-full text-left">
            <h2 class="text-lg font-semibold text-gray-800">
                Количество заказов клиента за {{ $currentMonth }}
            </h2>
            <svg id="trip-counts-arrow" class="h-5 w-5 transform transition-transform text-gray-500"
                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <div id="trip-counts-content" class="p-2 "> <!-- Убран класс hidden -->
        <div class="flex flex-wrap items-center gap-2 md:gap-4">
        </div>
        <!-- Количество поездок клиента -->
        <div class="bg-gray-50 p-4 rounded-lg mb-6">
            <div class="flex items-center">
                <span class="text-lg font-semibold text-gray-800">Общее число заказов клиента в этом месяце:</span>
                <button 
                    onclick="showClientTrips({{ $client->id }}, '{{ $startDate->format('Y-m') }}')"
                    class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                    {{ getClientTripsCountInMonthByVisitDate($client->id, $startDate) }}
                </button>
                 , в т.ч. со 100% скидкой: 
                    <span class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                        &nbsp;{{ getClientPaidTripsCountInMonthByVisitDate($client->id,$startDate) }}
                    </span>
            </div>
            <!-- Число поездок переданных в такси -->
            <div class="flex items-center mt-2">
                <span class="text-lg font-semibold text-gray-800">Число заказов, переданных оператору такси:</span>
                <button 
                    onclick="showClientTaxiSentTrips({{ $client->id }}, '{{ $startDate->format('Y-m') }}')"
                    class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-yellow-100 text-yellow-800 hover:bg-yellow-200 transition-colors">
                    {{ getClientTaxiSentTripsCountInMonthByVisitDate($client->id, $startDate) }}
                </button>
            </div>
            <!-- Число фактических поездок в месяц -->
            <div class="flex items-center mt-2">
                <span class="text-lg font-semibold text-gray-800">Число закрытых поездок в месяц:</span>
                <button 
                    onclick="showClientActualTrips({{ $client->id }}, '{{ $startDate->format('Y-m') }}')"
                    class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-bold bg-green-100 text-green-800 hover:bg-green-200 transition-colors">
                    {{ getClientActualTripsCountInMonthByVisitDate($client->id, $startDate) }}
                </button>
            </div>
        </div>
    </div>
</div>