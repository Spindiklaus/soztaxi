<x-app-layout>
    <!-- Заголовок и навигация (опционально, можно убрать, если не нужна) -->
    <!--
    <div class="flex justify-between items-center mb-6">
        <h1 class="text-3xl font-bold text-gray-800">Группировка заказов</h1>
        ...
    </div>
    -->

    <!-- Основной контент -->
    <div class="container mx-auto py-8 px-4 sm:px-6 lg:px-8">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-bold text-gray-800 mb-6">Группировка заказов на день</h2>

            <form action="{{ route('orders.grouping.show') }}" method="POST" class="space-y-6">
                @csrf
                <div class="space-y-2">
                    <label for="grouping_date" class="block text-sm font-medium text-gray-700">Выберите дату:</label>
                    <input 
                        type="date" 
                        name="grouping_date" 
                        id="grouping_date" 
                        value="{{ old('grouping_date', now()->format('Y-m-d')) }}" 
                        required 
                        class="mt-1 block w-full max-w-xs px-3 py-2 border border-gray-300 rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"
                    >
                </div>

                <button 
                    type="submit" 
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
                >
                    Найти заказы для группировки
                </button>
            </form>
        </div>
    </div>
</x-app-layout>