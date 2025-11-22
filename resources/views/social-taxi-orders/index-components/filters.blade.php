<!-- resources/views/social-taxi-orders/index-components/filters.blade.php -->

<form action="{{ route('social-taxi-orders.index') }}" method="GET" class="bg-white shadow rounded-lg mb-4">
    <!-- Заголовок аккордеона -->
    <div class="px-4 py-2 bg-gray-50 border-b border-gray-200">
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
    
    <!-- Отображение активных фильтров -->
    @php
        $activeFilters = [];
        $filterLabels = [
            'filter_pz_nom' => 'Номер заказа',
            'filter_type_order' => 'Тип заказа',
            'status_order_id' => 'Статус заказа',
            'filter_user_id' => 'Оператор',
            'client_fio' => 'ФИО клиента',
            'show_deleted' => 'Статус записей',
            'date_from' => 'Дата от',
            'date_to' => 'Дата до'
        ];
        
        $statusLabels = [
            '1' => 'Принят',
            '2' => 'Передан в такси',
            '3' => 'Отменён',
            '4' => 'Закрыт'
        ];
        
        $typeLabels = [
            '1' => 'Соцтакси',
            '2' => 'Легковое авто',
            '3' => 'ГАЗель'
        ];
        
        $operatorNames = [];
        foreach($operators ?? [] as $operator) {
            $operatorNames[$operator->id] = $operator->name . ($operator->litera ? ' (' . $operator->litera . ')' : '');
        }
        
        foreach (request()->all() as $key => $value) {
            if (in_array($key, ['filter_pz_nom', 'filter_type_order', 'status_order_id', 'filter_user_id', 'client_fio', 'show_deleted', 'date_from', 'date_to']) && !empty($value)) {
                if ($key === 'status_order_id' && isset($statusLabels[$value])) {
                    $activeFilters[] = $filterLabels[$key] . ': ' . $statusLabels[$value];
                } elseif ($key === 'filter_type_order' && isset($typeLabels[$value])) {
                    $activeFilters[] = $filterLabels[$key] . ': ' . $typeLabels[$value];
                } elseif ($key === 'filter_user_id' && isset($operatorNames[$value])) {
                    $activeFilters[] = $filterLabels[$key] . ': ' . $operatorNames[$value];
                } elseif ($key === 'show_deleted') {
                    $activeFilters[] = $filterLabels[$key] . ': ' . ($value == '1' ? 'Все (включая удаленные)' : 'Только активные');
                } elseif ($key !== 'sort' && $key !== 'direction' && $key !== 'page') {
                    $activeFilters[] = $filterLabels[$key] . ': ' . $value;
                }
            }
        }
    @endphp
    
    @if(!empty($activeFilters))
    <div class="px-4 py-2 bg-blue-50 border-b border-blue-100">
        <div class="flex flex-wrap items-center gap-2">
            <span class="text-sm font-medium text-blue-700">Активные фильтры:</span>
            @foreach($activeFilters as $filter)
                <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                    {{ $filter }}
                </span>
            @endforeach
            <a href="{{ route('social-taxi-orders.index', ['sort' => $sort ?? 'pz_data', 'direction' => $direction ?? 'desc']) }}"
               class="ml-2 text-xs text-blue-600 hover:text-blue-800">
                Сбросить все
            </a>
        </div>
    </div>
    @endif
    
    <!-- Содержимое фильтров (скрыто по умолчанию) -->
    <div id="filters-content" class="p-4 hidden">
        <div class="grid grid-cols-1 md:grid-cols-6 gap-4 mb-0">
            <!-- Скрытое поле для сохранения параметров сортировки -->
            <input type="hidden" name="sort" value="{{ $sort ?? 'pz_data' }}">
            <input type="hidden" name="direction" value="{{ $direction ?? 'desc' }}">
                
            <div>
                <label for="filter_pz_nom" class="block text-sm font-medium text-gray-700">Номер заказа</label>
                <input type="text" name="filter_pz_nom" id="filter_pz_nom" 
                       value="{{ request('filter_pz_nom') }}" 
                       placeholder="%Поиск%"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>    

            <div class="md:col-span-2">
                <label for="client_fio" class="block text-sm font-medium text-gray-700">ФИО клиента</label>
                <input type="text" name="client_fio" id="client_fio" 
                       value="{{ request('client_fio') }}" 
                       placeholder="%Поиск по ФИО%"
                       class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
            </div>

            <div>
                <label for="filter_type_order" class="block text-sm font-medium text-gray-700">Тип заказа</label>
                <select name="filter_type_order" id="filter_type_order" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Все</option>
                    <option value="1" {{ request('filter_type_order') == '1' ? 'selected' : '' }}>Соцтакси</option>
                    <option value="2" {{ request('filter_type_order') == '2' ? 'selected' : '' }}>Легковое авто</option>
                    <option value="3" {{ request('filter_type_order') == '3' ? 'selected' : '' }}>ГАЗель</option>
                </select>
            </div>

            <div>
                <label for="status_order_id" class="block text-sm font-medium text-gray-700">Статус заказа</label>
                <select name="status_order_id" id="status_order_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Все статусы</option>
                    <option value="1" {{ request('status_order_id') == '1' ? 'selected' : '' }}>Принят</option>
                    <option value="2" {{ request('status_order_id') == '2' ? 'selected' : '' }}>Передан в такси</option>
                    <option value="3" {{ request('status_order_id') == '3' ? 'selected' : '' }}>Отменён</option>
                    <option value="4" {{ request('status_order_id') == '4' ? 'selected' : '' }}>Закрыт</option>
                </select>
            </div>

            <div>
                <label for="show_deleted" class="block text-sm font-medium text-gray-700">Статус записей</label>
                <select name="show_deleted" id="show_deleted" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="0" {{ (request('show_deleted', '0') == '0' || is_null(request('show_deleted'))) ? 'selected' : '' }}>Только активные</option>
                    <option value="1" {{ request('show_deleted') == '1' ? 'selected' : '' }}>Все (включая удаленные)</option>
                </select>
            </div>

            <!-- фильтр по операторам -->
            <div class="md:col-span-2">
                <label for="filter_user_id" class="block text-sm font-medium text-gray-700">Оператор</label>
                <select name="filter_user_id" id="filter_user_id" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    <option value="">Все операторы</option>
                    @foreach($operators as $operator)
                        <option value="{{ $operator->id }}" {{ request('filter_user_id') == $operator->id ? 'selected' : '' }}>
                            {{ $operator->name }} ({{ $operator->litera ?? 'Без литеры' }})
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="md:col-span-2">
                <label class="block text-sm font-medium text-gray-700">Дата поездки:</label>
                <div class="grid grid-cols-2 gap-2">
                    <div>
                        <input type="date" name="date_from" id="date_from" 
                               value="{{ request('date_from', '2025-01-01') }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                    <div>
                        <input type="date" name="date_to" id="date_to" 
                               value="{{ request('date_to', date('Y-m-d', strtotime('+6 months'))) }}" 
                               class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                    </div>
                </div>
            </div>

            <div class="md:col-span-6 flex items-end space-x-2">
                <button type="submit"
                        class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 
                        focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                    Применить фильтр
                </button>
                @php
                    $baseParams = request()->only(['sort', 'direction']);
                    $resetParams = array_merge($baseParams, [
                        'date_from' => '2025-01-01',
                        'date_to' => \Carbon\Carbon::now()->addMonths(6)->format('Y-m-d'),
                        'show_deleted' => '0'
                     ]);
                @endphp
                <a href="{{ route('social-taxi-orders.index', $resetParams) }}"
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

// Автоматически скрывать фильтры после выбора (если не скрыты)
document.addEventListener('DOMContentLoaded', function() {
    const form = document.querySelector('form');
    if (form) {
        form.addEventListener('submit', function() {
            const content = document.getElementById('filters-content');
            const arrow = document.getElementById('filter-arrow');
            
            if (!content.classList.contains('hidden')) {
                content.classList.add('hidden');
                arrow.classList.remove('rotate-180');
            }
        });
    }
});
</script>