<!-- resources/views/social-taxi-orders/close.blade.php -->
<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Закрытие заказов </h1>
                
                <a href="{{ route('social-taxi-orders.index', $urlParams) }}" 
                   class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Все заказы
                </a>
            </div>

            @include('social-taxi-orders.close-components.filter')

            <!-- Форма действий -->
            <div class="flex flex-wrap gap-2 mb-4">
            <form action="{{ route('social-taxi-orders.close.bulk-close') }}" method="POST" class="mb-4">
                @csrf
                <input type="hidden" name="visit_date_from" value="{{ request('visit_date_from', date('Y-m-d')) }}">
                <input type="hidden" name="visit_date_to" value="{{ request('visit_date_to', date('Y-m-d')) }}">
                <input type="hidden" name="taxi_id" value="{{ request('taxi_id') }}">

                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150"
                        onclick="return confirm('Вы уверены, что хотите закрыть все отмеченные заказы?')"
                        title="закрыть отмеченные">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" />
                    </svg>
                    Закрыть отмеченные
                </button>
                @include('social-taxi-orders.close-components.table')
            </form>
            </div>

            <!-- Пагинация -->
            <div class="mt-4 mb-2">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>