<!-- resources/views/social-taxi-orders/open.blade.php -->
<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-2">
                <h1 class="text-3xl font-bold text-gray-800">
                    Открытие заказов 
                     <span class="text-lg font-normal text-gray-600">
                        (всего: {{ $totalOrders ?? $orders->total() }})
                    </span>
                </h1>
                
                <a href="{{ route('social-taxi-orders.index', $urlParams) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Все заказы
                </a>
            </div>

            @include('social-taxi-orders.open-components.filter') <!-- Можно использовать тот же фильтр -->

            <!-- Форма действий -->
            <div class="flex flex-wrap gap-2 mb-2">
                <!-- Форма только для открытия -->
                <form action="{{ route('social-taxi-orders.open.bulk-unset') }}" method="POST" class="mb-4">
                    @csrf
                    <input type="hidden" name="date_from" value="{{ request('date_from', date('Y-m-d')) }}">
                    <input type="hidden" name="date_to" value="{{ request('date_to', date('Y-m-d')) }}">
                    <input type="hidden" name="taxi_id" value="{{ request('taxi_id') }}">

                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-yellow-600 border border-transparent rounded-md font-semibold 
                            text-white hover:bg-yellow-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-yellow-500 
                            transition ease-in-out duration-150 mb-2"
                            onclick="return confirm('Вы уверены, что хотите открыть все отмеченные заказы?')"
                            title="открыть отмеченные">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                        </svg>
                        Открыть отмеченные
                    </button>
                    @include('social-taxi-orders.open-components.table')
                </form>
            </div>


            <!-- Пагинация -->
            <div class="mt-4 mb-2">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>
