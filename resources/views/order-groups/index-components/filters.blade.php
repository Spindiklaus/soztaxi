<!-- resources/views/order-groups/index-components/filters.blade.php -->

<form action="{{ route('order-groups.index') }}" method="GET" class="bg-white shadow rounded-lg mb-4">
    <!-- Заголовок аккордеона -->
    <div class="px-4 py-3 bg-gray-50 border-b border-gray-200">
        <button type="button" 
                onclick="toggleFilters()"
                class="flex items-center justify-between w-full text-left text-sm font-medium text-gray-700 hover:text-gray-900">
            <span>Фильтры</span>
            <svg id="filter-arrow" class="h-5 w-5 transform transition-transform" 
                 xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20" fill="currentColor">
                <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
            </svg>
        </button>
    </div>
    
    <!-- Содержимое фильтров (скрыто по умолчанию) -->
    <div id="filters-content" class="p-2 hidden">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
            <!-- Скрытое поле для сохранения параметров сортировки -->
            <input type="hidden" name="sort" value="{{ request('sort', 'visit_date') }}">
            <input type="hidden" name="direction" value="{{ request('direction', 'desc') }}">

            <div>
                <label for="date_from" class="block text-sm font-medium text-gray-700">Дата поездки от</label>
                <input type="date" name="date_from" id="date_from" 
                       value="{{ request('date_from', '2025-01-01') }}" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="date_to" class="block text-sm font-medium text-gray-700">Дата поездки до</label>
                <input type="date" name="date_to" id="date_to" 
                       value="{{ request('date_to', date('Y-m-d', strtotime('+6 months'))) }}" 
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="filter_name" class="block text-sm font-medium text-gray-700">Название группы</label>
                <input type="text" name="filter_name" id="filter_name" 
                       value="{{ request('filter_name') }}" 
                       placeholder="%Поиск%"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div class="md:col-span-2 flex items-end space-x-2">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                    Применить фильтр
                </button>
                @php
                    // Собираем параметры для сброса - оставляем только базовые (сортировка)
                    $baseParams = request()->only(['sort', 'direction']);
                @endphp
                <a href="{{ route('order-groups.index', $baseParams) }}"
                    class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-gray-800 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                    Сбросить фильтры
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

// Показываем фильтры, если есть активные фильтры
document.addEventListener('DOMContentLoaded', function() {
    // Проверяем, есть ли активные пользовательские фильтры
    const hasUserFilters = {{ 
        collect(request()->except(['sort', 'direction', 'page']))->filter(function($value, $key) {
            // Исключаем параметры по умолчанию или пустые значения
            return !empty($value);
        })->isNotEmpty() ? 'true' : 'false' 
    }};
    
    if (hasUserFilters) {
        document.getElementById('filters-content').classList.remove('hidden');
        document.getElementById('filter-arrow').classList.add('rotate-180');
    }
});
</script>