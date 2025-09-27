<!-- resources/views/social-taxi-orders/taxi-components/filters.blade.php -->
<form action="{{ route('taxi-orders.index') }}" method="GET" class="bg-white shadow rounded-lg mb-4">
    <!-- Заголовок аккордеона -->
    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
        <button type="button" 
                onclick="toggleFilters()"
                class="flex items-center justify-between w-full text-left text-sm font-medium text-gray-700 hover:text-gray-900">
            <span>Фильтры по дате поездки</span>
            <svg id="filter-arrow" class="h-5 w-5 transform rotate-180 transition-transform" 
                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
    
    <!-- Содержимое фильтров (всегда показывается) -->
    <div id="filters-content" class="p-4">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <!-- Скрытое поле для сохранения параметров сортировки -->
            <input type="hidden" name="sort" value="{{ $sort ?? 'visit_data' }}">
            <input type="hidden" name="direction" value="{{ $direction ?? 'asc' }}">

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Дата поездки</label>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <input type="date" name="visit_date_from" id="visit_date_from" 
                               value="{{ request('visit_date_from', date('Y-m-d')) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <input type="date" name="visit_date_to" id="visit_date_to" 
                               value="{{ request('visit_date_to', date('Y-m-d')) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
                <p class="mt-1 text-xs text-gray-500">Показываются только активные заказы (не удаленные и не отмененные)</p>
            </div>

            <div class="md:col-span-2 flex items-end space-x-2">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                    Применить фильтр
                </button>
                @php
                    // Собираем параметры для сброса - только базовые + сегодняшняя дата
                    $resetParams = [
                        'sort' => $sort ?? 'visit_data',
                        'direction' => $direction ?? 'asc',
                        'visit_date_from' => date('Y-m-d'),
                        'visit_date_to' => date('Y-m-d')
                     ];
                @endphp
                <a href="{{ route('taxi-orders.index', $resetParams) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-gray-800 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                    Сегодня
                </a>
            </div>
        </div>
    </div>
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