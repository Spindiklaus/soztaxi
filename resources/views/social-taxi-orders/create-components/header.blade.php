<!-- resources/views/social-taxi-orders/create-components/header.blade.php -->
<div class="flex justify-between items-center mb-6">
    <h1 class="text-3xl font-bold text-gray-800">
        @if(isset($isCopying) && $isCopying)
            Копирование заказа: 
            <span class="{{ getOrderTypeColor($type) }} font-medium">
                {{ getOrderTypeName($type) }}
            </span>
            <span class="text-lg text-gray-600 ml-2">
                (оригинал №{{ $originalOrderNumber ?? '' }})
            </span>
        @else
            Создание нового заказа: 
            <span class="{{ getOrderTypeColor($type) }} font-medium">
                {{ getOrderTypeName($type) }}
            </span>
        @endif
    </h1>

    <a href="{{ route('social-taxi-orders.index', $backUrlParams) }}" 
       class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Назад к списку
    </a>
</div>