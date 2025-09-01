<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок и кнопки -->
            @include('social-taxi-orders.components.header')

            <!-- Фильтры -->
            @include('social-taxi-orders.components.filters')
            <!-- Пагинация -->
            <div class="mt-4 mb-2">
                {{ $orders->appends(request()->all())->links() }}
            </div>

            <!-- Таблица с сортировкой -->
            @include('social-taxi-orders.components.table')

            <!-- Пагинация -->
            <div class="mt-4">
                {{ $orders->appends(request()->all())->links() }}
            </div>
        </div>
    </div>
</x-app-layout>