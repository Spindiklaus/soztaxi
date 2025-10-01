<div id="closure" class="tab-content hidden">
    <div class="bg-gray-50 p-4 rounded-lg mb-6">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Закрытие (отмена) заказа</h2>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-gray-700">Дата отмены</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">
                    {{ $order->cancelled_at ? $order->cancelled_at->format('d.m.Y H:i') : 'Не указана' }}
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700">Дата закрытия</label>
                <div class="mt-1 bg-gray-100 p-2 rounded-md">
                    {{ $order->closed_at ? $order->closed_at->format('d.m.Y H:i') : 'Не указана' }}
                </div>
            </div>
            
            
        </div>
    </div>
    
    <div class="bg-gray-50 p-4 rounded-lg">
        <h2 class="text-lg font-semibold text-gray-800 mb-4">Комментарии</h2>
        
        <div class="bg-white p-4 rounded-md border">
            <p class="text-sm text-gray-600">{!! nl2br(e($order->komment ?? 'Комментарии отсутствуют')) !!}</p>
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
                            @php
                                $historyStatus = $history->statusOrder;
                                $historyColorClass = $historyStatus && !empty($historyStatus->color) ? $historyStatus->color : 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $historyColorClass }}">
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