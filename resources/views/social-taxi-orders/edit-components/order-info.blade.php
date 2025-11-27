<!-- resources/views/social-taxi-orders/edit-components/order-info.blade.php -->
<div class="bg-gray-50 p-4 rounded-lg mb-2">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Номер заказа</label>
            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                {{ $order->pz_nom }}
            </div>
            <!-- Скрытое поле для передачи номера в форму -->
            <input type="hidden" name="pz_nom" value="{{ $order->pz_nom }}">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Дата и время приема заказа</label>
            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                {{ $order->pz_data->format('d.m.Y H:i:s') }}
            </div>
            <!-- Скрытое поле для передачи даты в форму -->
            <input type="hidden" name="pz_data" value="{{ $order->pz_data->format('Y-m-d H:i') }}">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Тип заказа</label>
            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium {{ getOrderTypeColor($order->type_order) }}">
                {{ getOrderTypeName($order->type_order) }}
            </div>
            <!-- Скрытое поле для передачи типа в форму -->
            <input type="hidden" name="type_order" value="{{$order->type_order }}">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700">Оператор приема</label>
            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                {{ $order->user->name ?? 'Неизвестный оператор' }} 
                @if($order->user)
                    ({{ $order->user->litera ?? 'UNK' }})
                @endif
            </div>
            <!-- Скрытое поле для передачи ID оператора в форму -->
            <input type="hidden" name="user_id" value="{{ $order->user_id}}">
        </div>
    </div>
    
    <!-- Количество поездок клиента - в одной строке -->
    <div id="client-trips-info" class="mt-4 p-3 bg-white rounded-lg border border-gray-200" style="display: block;">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center">
                <span class="text-sm font-medium text-gray-700">Всего поездок:</span>
                <button 
                    id="client-trips-button"
                    type="button"
                    onclick="showClientTrips({{$order->client_id}}, '{{$order->visit_data->format('Y-m')}}')"
                    class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                     <span class="loading">Загрузка...</span>
                </button>
                , в т.ч. со 100% скидкой: 
                <span class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                    &nbsp;{{ getClientFreeTripsCountInMonthByVisitDate($order->client_id, $order->visit_data) }}
                </span>
            </div>

            <div class="flex items-center">
                <span class="text-sm font-medium text-gray-700">Фактических:</span>
                <button 
                    id="client-actual-trips-button"
                    type="button"
                    onclick="showClientActualTrips({{$order->client_id}}, '{{$order->visit_data->format('Y-m')}}')"
                    class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 hover:bg-green-200 transition-colors">
                     <span class="loading">Загрузка...</span>
                </button>
            </div>

            <div class="flex items-center">
                <span class="text-sm font-medium text-gray-700">Переданных в такси:</span>
                <button 
                    id="client-taxi-sent-trips-button"
                    type="button"
                    onclick="showClientTaxiSentTrips({{$order->client_id}}, '{{$order->visit_data->format('Y-m')}}')"
                    class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 hover:bg-yellow-200 transition-colors">
                     <span class="loading">Загрузка...</span>
                </button>
            </div>
        </div>
    </div>
</div>
