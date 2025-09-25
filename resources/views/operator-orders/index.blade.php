<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-6">
                @php
                    $typeName = '';
                    $typeColor = '';
                    switch(request('type_order')) {
                        case 1: 
                            $typeName = 'Соцтакси';
                            $typeColor = 'text-blue-600';
                            break;
                        case 2: 
                            $typeName = 'Легковое авто';
                            $typeColor = 'text-green-600';
                            break;
                        case 3: 
                            $typeName = 'ГАЗель';
                            $typeColor = 'text-yellow-600';
                            break;
                    }
                @endphp
                <h1 class="text-3xl font-bold text-gray-800">Мои заказы: <span class="{{ $typeColor }}">{{ $typeName }}</span></h1>
                
                <a href="{{ route('social-taxi-orders.create.by-type', array_merge(['type' => request('type_order')], $urlParams)) }}" 
                   class="inline-flex items-center px-4 py-2 bg-blue-600 text-white rounded-md hover:bg-blue-700">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6" />
                    </svg>
                    Добавить заказ
                </a>
            </div>
            
            <!-- Фильтры -->
            @include('operator-orders.index-components.filters')
            <!-- Пагинация -->
            <div class="mt-4 mb-2"">
                {{ $orders->links() }}
            </div>
            <!-- Таблица заказов -->
            @include('operator-orders.index-components.table')

            <!-- Пагинация -->
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>