<!-- Предварительная информация о заказе, заголовок -->
<!-- resources/views/social-taxi-orders/create-components/order-info.blade.php -->
<div class="bg-gray-50 p-4 rounded-lg mb-6">
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div>
            <label class="block text-sm font-medium text-gray-700">Номер заказа</label>
            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                {{ $orderNumber }}
            </div>
            <p class="mt-1 text-xs text-gray-500">Устанавливается автоматически</p>
            <!-- Скрытое поле для передачи номера в форму -->
            <input type="hidden" name="pz_nom" value="{{ $orderNumber }}">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Дата и время приема заказа</label>
            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                {{ $orderDateTime->format('d.m.Y H:i:s') }}
            </div>
            <p class="mt-1 text-xs text-gray-500">Устанавливается автоматически</p>
            <!-- Скрытое поле для передачи даты в форму -->
            <input type="hidden" name="pz_data" value="{{ $orderDateTime->format('Y-m-d H:i:s') }}">
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-700">Тип заказа</label>
            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium {{ getOrderTypeColor($type) }}">
                {{ getOrderTypeName($type) }}
            </div>
            <!-- Скрытое поле для передачи типа в форму -->
            <input type="hidden" name="type_order" value="{{ $type }}">
        </div>
        
        <div>
            <label class="block text-sm font-medium text-gray-700">Оператор</label>
            <div class="mt-1 bg-gray-100 p-2 rounded-md font-medium">
                {{ auth()->user()->name ?? 'Неизвестный оператор' }} ({{ auth()->user()->litera ?? 'UNK' }})
            </div>
            <!-- Скрытое поле для передачи ID оператора в форму -->
            <input type="hidden" name="user_id" value="{{ auth()->id() ?? 1 }}">
        </div>
    </div>
    
    <!-- Количество поездок клиента - в одной строке -->
    <div id="client-trips-info" class="mt-4 p-3 bg-white rounded-lg border border-gray-200" style="display: none;">
        <div class="flex flex-wrap items-center gap-4">
            <div class="flex items-center">
                <span class="text-sm font-medium text-gray-700">Всего поездок:</span>
                <button 
                    id="client-trips-button"
                    type="button"
                    onclick="showClientTrips(0, '')"
                    class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-800 hover:bg-blue-200 transition-colors">
                    0
                </button>
            </div>

            <div class="flex items-center">
                <span class="text-sm font-medium text-gray-700">Фактических:</span>
                <button 
                    id="client-actual-trips-button"
                    type="button"
                    onclick="showClientActualTrips(0, '')"
                    class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-green-100 text-green-800 hover:bg-green-200 transition-colors">
                    0
                </button>
            </div>

            <div class="flex items-center">
                <span class="text-sm font-medium text-gray-700">Передано в такси:</span>
                <button 
                    id="client-taxi-sent-trips-button"
                    type="button"
                    onclick="showClientTaxiSentTrips(0, '')"
                    class="ml-2 inline-flex items-center px-2 py-1 rounded-full text-xs font-bold bg-yellow-100 text-yellow-800 hover:bg-yellow-200 transition-colors">
                    0
                </button>
            </div>
        </div>
    </div>
</div>