<!-- resources/views/social-taxi-orders/taxi-components/filters.blade.php -->
<form action="{{ route('taxi-orders.index') }}" method="GET" class="bg-white shadow rounded-lg mb-4">
    <!-- Заголовок аккордеона -->
    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
        <button type="button" 
                onclick="toggleFilters()"
                class="flex items-center justify-between w-full text-left text-sm font-medium text-gray-700 hover:text-gray-900">
            <span>Фильтры</span>
            <svg id="filter-arrow" class="h-5 w-5 transform rotate-180 transition-transform" 
                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>

    <!-- Содержимое фильтров (всегда показывается) -->
    <div id="filters-content" class="p-4">
        <div class="flex flex-wrap gap-4 mb-4">
            <!-- Скрытое поле для сохранения параметров сортировки -->
            <input type="hidden" name="sort" value="{{ $sort ?? 'visit_data' }}">
            <input type="hidden" name="direction" value="{{ $direction ?? 'asc' }}">

                    <div class="flex flex-col">
                        <label class="block text-sm font-medium text-gray-700">Дата поездки</label>
                        <div class="flex gap-2">
                            <input type="date" name="date_from" id="date_from" 
                                    value="{{ request('date_from', date('Y-m-d')) }}" 
                                    class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            <input type="date" name="date_to" id="date_to" 
                                   value="{{ request('date_to', date('Y-m-d')) }}" 
                                   class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        </div>
                    </div>

                    <!-- Фильтр по такси -->
                    <div class="flex flex-col">
                        <label for="taxi_id" class="block text-sm font-medium text-gray-700">Оператор такси</label>
                        <select name="taxi_id" id="taxi_id" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                            @foreach($taxis as $taxi)
                            <option value="{{ $taxi->id }}" {{ request('taxi_id') == $taxi->id ? 'selected' : '' }}>
                                {{ $taxi->name }} (#{{ $taxi->id }})
                            </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="flex flex-col">
                        <label for="taxi_sent_at" class="block text-sm font-medium text-gray-700">Дата передачи в такси</label>
                        <input type="datetime-local" name="taxi_sent_at" id="taxi_sent_at" 
                               value="{{ request('taxi_sent_at',  now()->format('Y-m-d\TH:i')) }}"  
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>


                    <div class="flex items-end">
                        <button type="submit"
                                class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                            Применить фильтр
                        </button>
                    </div>
        </div>
    </div>
    <p class="mt-1 text-xs text-gray-500">Показываются только активные заказы (не удаленные и не отмененные)</p>

</form>
<script>
    function toggleFilters() {
    const content = document.getElementById('filters-content');
    const arrow = document.getElementById('filter-arrow');

    if (content.classList.contains('hidden')) {
        content.classList.remove('hidden');
        arrow.classList.add('rotate-180');
    } else {
        content.classList.add('hidden');
        arrow.classList.remove('rotate-180');
    }
    }
</script>