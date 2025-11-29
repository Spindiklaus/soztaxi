<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-[1536px]  mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок и кнопки -->
            @include('social-taxi-orders.index-components.header')

            <!-- Фильтры -->
            @include('social-taxi-orders.index-components.filters')
            <!-- Пагинация -->
<!--            <div class="mt-4 mb-2">
                {{-- $orders->appends(request()->all())->links() --}}
            </div>-->

            <!-- Таблица с сортировкой -->
            @include('social-taxi-orders.index-components.table')

            <!-- Пагинация -->
            <div class="mt-4">
                {{ $orders->appends(request()->all())->links() }}
            </div>
        </div>
    </div>
</x-app-layout>