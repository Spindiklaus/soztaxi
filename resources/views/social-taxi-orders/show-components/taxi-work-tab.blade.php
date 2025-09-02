<div id="taxi-work" class="tab-content hidden">
    <div class="bg-gray-50 p-4 rounded-lg mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Работа с такси</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Дата передачи в такси</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">
                    {{ $order->taxi_sent_at ? $order->taxi_sent_at->format('d.m.Y H:i') : 'Не указана' }}
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Оператор такси</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">
                    {{ $order->taxi_id ? 'Оператор #' . $order->taxi_id : 'Не выбран' }}
                </div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Адрес отправки</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->adres_trips_id ?? 'Не указано' }}</div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Дистанция поездки</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->taxi_way ?? '0' }} км</div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Цена поездки</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->taxi_price ?? '0' }} руб.</div>
            </div>
            
            <div>
                <label class="block text-sm font-medium text-gray-700">Статус поездки</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">
                    @if($order->cancelled_at)
                        Отменён
                    @elseif($order->closed_at)
                        Закрыт
                    @else
                        Выполняется
                    @endif
                </div>
            </div>
        </div>
    </div>
    
    <div class="bg-gray-50 p-4 rounded-lg">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">История статусов</h2>
        
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Статус</th>
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
                            {{ $history->user_id ? 'Оператор #' . $history->user_id : 'Не указан' }}
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