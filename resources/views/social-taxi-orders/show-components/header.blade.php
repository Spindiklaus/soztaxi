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
        <a href="{{ route('social-taxi-orders.index', $backUrlParams) }}" 
           class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
            <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
            </svg>
            Назад к списку
        </a>
        
        @if(isset($order) && $order->exists)
            @if(!$order->deleted_at)
                @php
                    $status = $order->currentStatus->statusOrder;
                @endphp
                @if($status->id == 1)
                    <a href="{{ route('social-taxi-orders.edit', $order) }}" 
                       class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Редактировать
                    </a>
                @else
                    <button type="button" 
                            class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-500 rounded-md cursor-not-allowed" 
                            disabled 
                            title="Редактирование возможно только для заказов со статусом 'Принят'">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                        </svg>
                        Редактировать
                    </button>
                @endif
            @else
                <button type="button" 
                        class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-500 rounded-md cursor-not-allowed" 
                        disabled 
                        title="Невозможно редактировать удаленный заказ">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                    </svg>
                    Редактировать
                </button>
            @endif
        @endif
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
