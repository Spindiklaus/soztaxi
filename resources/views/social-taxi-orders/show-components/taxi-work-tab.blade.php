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
                    {{ $order->taxi->name }} (#{{ $order->taxi->id }})
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

    <div class="bg-gray-50 p-4 rounded-lg">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">История статусов заказа</h2>

        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Назначенный статус</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Оператор</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
                    @forelse ($order->statusHistory as $history)
                    <tr>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            {{ $history->created_at->format('d.m.Y H:i') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">
                                {{ $history->statusOrder->name ?? 'Неизвестный статус' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                            @if($history->user)
                                {{ $history->user->name }}
                            @elseif($history->user_id)
                                Оператор #{{ $history->user_id }}
                            @else
                                Не указан
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-6 py-4 text-center text-gray-500">История отсутствует</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>