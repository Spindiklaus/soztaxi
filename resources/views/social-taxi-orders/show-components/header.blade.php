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
            <a href="{{ route('social-taxi-orders.edit', $order) }}" 
               class="inline-flex items-center px-4 py-2 bg-yellow-500 text-white rounded-md hover:bg-yellow-600">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                </svg>
                Редактировать
            </a>
        @endif
    </div>
</div>