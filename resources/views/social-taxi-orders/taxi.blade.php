<!-- resources/views/social-taxi-orders/taxi.blade.php -->
<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Заказы для передачи в Такси</h1>
                
                <a href="{{ route('social-taxi-orders.index', $urlParams) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Все заказы
                </a>
            </div>
            
            @include('social-taxi-orders.taxi-components.filters')
            
            <!-- Пагинация -->
            <div class="mt-4 mb-2">
                {{ $orders->links() }}
            </div>

            @include('social-taxi-orders.taxi-components.table')

            <!-- Пагинация -->
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>