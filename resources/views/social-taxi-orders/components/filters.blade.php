<form action="{{ route('social-taxi-orders.index') }}" method="GET" class="bg-white shadow rounded-lg mb-4">
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
    <div id="filters-content" class="p-4 hidden">
        <div class="grid grid-cols-1 md:grid-cols-5 gap-4 mb-4">
            <!-- Скрытое поле для сохранения параметров сортировки -->
            @foreach(request()->except(['pz_nom', 'type_order', 'show_deleted', 'page', 'date_from', 'date_to']) as $key => $value)
                <input type="hidden" name="{{ $key }}" value="{{ $value }}">
            @endforeach

            <div>
                <label for="filter_pz_nom" class="block text-sm font-medium text-gray-700">Номер заказа</label>
                <input type="text" name="pz_nom" id="filter_pz_nom" value="{{ request('pz_nom') }}" placeholder="%Поиск%" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="filter_type_order" class="block text-sm font-medium text-gray-700">Тип заказа</label>
                <select name="type_order" id="filter_type_order" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Все</option>
                    <option value="1" {{ request('type_order') == '1' ? 'selected' : '' }}>Соцтакси</option>
                    <option value="2" {{ request('type_order') == '2' ? 'selected' : '' }}>Легковое авто</option>
                    <option value="3" {{ request('type_order') == '3' ? 'selected' : '' }}>ГАЗель</option>
                </select>
            </div>

            <div>
                <label for="show_deleted" class="block text-sm font-medium text-gray-700">Статус записей</label>
                <select name="show_deleted" id="show_deleted" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="0" {{ (request('show_deleted', '0') == '0' || is_null(request('show_deleted'))) ? 'selected' : '' }}>Только активные</option>
                    <option value="1" {{ request('show_deleted') == '1' ? 'selected' : '' }}>Все (включая удаленные)</option>
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Дата приема заказа (c - по)</label>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <input type="date" name="date_from" id="date_from" 
                               value="{{ request('date_from', '2016-08-01') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <input type="date" name="date_to" id="date_to" 
                               value="{{ request('date_to', date('Y-m-d')) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>
        </div>

        <div class="flex justify-end space-x-2">
            <button type="submit"
                    class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                Применить фильтр
            </button>
            <a href="{{ route('social-taxi-orders.index', array_merge(request()->only(['sort', 'direction', 'show_deleted']), ['date_from' => '2016-08-01', 'date_to' => date('Y-m-d')])) }}"
               class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-gray-800 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                Сбросить фильтры
            </a>
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
    const hasActiveFilters = {{ request()->hasAny(['pz_nom', 'type_order', 'date_from', 'date_to']) ? 'true' : 'false' }};
    if (hasActiveFilters) {
        document.getElementById('filters-content').classList.remove('hidden');
        document.getElementById('filter-arrow').classList.add('rotate-180');
    }
});
</script>