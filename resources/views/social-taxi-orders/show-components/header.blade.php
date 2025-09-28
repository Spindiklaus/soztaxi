<div class="flex justify-between items-center mb-6">
    <div>
        <h1 class="text-3xl font-bold text-gray-800">Заказ №{{ $order->pz_nom ?? 'Неизвестный'}}</h1>
        <div class="mt-2 flex items-center">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ getOrderTypeBadgeColor($order->type_order) }}">
                {{ getOrderTypeName($order->type_order) ?? 0}}
            </span>
            @if($order->deleted_at)
                <span class="ml-2 inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-red-100 text-red-800">
                    Удален
                </span>
            @endif
        </div>
    </div>
    
    <div class="flex space-x-2">
        <!-- Кнопка "Назад к списку" с сохранением параметров -->
        @php
        
        \Log::info('Debug backUrlParams', [
        'backUrlParams' => $backUrlParams ?? [],
        'request_all' => request()->all(),
        'session_taxi_params' => session('taxi_filter_params', []),
        'referer' => request()->headers->get('referer')
    ]);
        
        
            $backToOperator = request('back_to_operator');
            $operatorType = request('operator_type');
            $fromTaxiPage = request('from_taxi_page', 0); // Проверяем, откуда пришли
            
            if ($backToOperator && $operatorType) {
                $backRoute = route($backToOperator, array_merge(['type_order' => $operatorType], $backUrlParams ?? []));
            } elseif ($fromTaxiPage) {
                // Если пришли со страницы такси, возвращаемся туда
                $backRoute = route('taxi-orders.index', $backUrlParams ?? []); 
            } else {
                $backRoute = route('social-taxi-orders.index', $backUrlParams ?? []);
            }
        @endphp
       
        <a href="{{ $backRoute }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
            Назад к списку
        </a>
     </div>
</div>
<div class="border border-gray-200 rounded-lg p-4 mb-6 bg-white shadow">
                <h2 class="text-lg font-semibold text-gray-800 mb-4">Прием заказа:</h2>

                <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Дата приема заказа</label>
                        <div class="mt-1 bg-gray-100 p-2 rounded-md">
                            {{ $order->pz_data ? $order->pz_data->format('d.m.Y H:i') : 'Не указана' }}
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Номер заказа</label>
                        <div class="mt-1 bg-gray-100 p-2 rounded-md">{{ $order->pz_nom ?? 'Не указан' }}</div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Оператор</label>
                        <div class="mt-1 bg-gray-100 p-2 rounded-md">
                            @if($order->user)
                                {{ $order->user->name }} (#{{ $order->user_id }})
                            @elseif($order->user_id)
                                #{{ $order->user_id }}
                            @else
                                Не указан
                            @endif
                        </div>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-gray-700">Статус заказа</label>
                        @if($order->currentStatus && $order->currentStatus->statusOrder)
                            @php
                                $status = $order->currentStatus->statusOrder;
                                $colorClass = !empty($status->color) ? $status->color : 'bg-gray-100 text-gray-800';
                            @endphp
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                {{ $status->name }}
                            </span>
                        @else
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                Нет статуса
                            </span>
                        @endif
                    </div>    
                </div>
</div>
