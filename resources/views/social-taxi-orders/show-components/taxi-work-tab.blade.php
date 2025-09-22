<div id="taxi-work" class="tab-content hidden">
    <div class="bg-gray-50 p-4 rounded-lg mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Работа с такси</h2>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <!-- Первая строчка -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Дата передачи сведений (звонка) в такси</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">
                    {{ $order->taxi_sent_at ? $order->taxi_sent_at->format('d.m.Y H:i') : 'Не указана' }}
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Оператор такси</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">
                    @if($order->taxi)
                        {{ $order->taxi->name }} (#{{ $order->taxi->id }})
                    @else
                        Не выбран
                    @endif    
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Дата поездки</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">
                    {{ $order->visit_data ? $order->visit_data->format('d.m.Y H:i') : 'Не указана' }}
                </div>
            </div>
            
            <!-- Вторая строчка -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Откуда ехать</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->adres_otkuda ?? 'Не указано' }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Куда ехать</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->adres_kuda ?? 'Не указано' }}</div>
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-700">Обратный путь</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->adres_obratno ?? 'Не указано' }}</div>
            </div>
            
            <!-- Третья строчка -->
            <div>
                <label class="block text-sm font-medium text-gray-700">Цена по факту поездки</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ number_format($order->taxi_price ?? 0, 11, ',', ' ') }} руб.</div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Сумма к оплате</label>
                @if ($order->taxi_price!=0)
                    <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ number_format($order->taxi_price - $order->taxi_vozm ?? 0, 11, ',', ' ') }} руб.</div>
                @endif    
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Сумма к возмещению</label>
                @if ($order->taxi_price!=0)
                    <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ number_format($order->taxi_vozm ?? 0, 11, ',', ' ') }} руб.</div>
                @endif   
            </div>
            
        </div>
    </div>
  
</div>