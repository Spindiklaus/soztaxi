<x-app-layout>
 <div class="max-w-7xl mx-auto px-4 py-2">
    <!-- Кнопка "Назад" -->
    <a href="
       @if($operatorRoute)
            {{ route($operatorRoute) . '?' . http_build_query($urlParams) }}
        @else
            {{ route('social-taxi-orders.index') . '?' . http_build_query($urlParams) }} 
        @endif
        "
       class="mb-2 inline-flex items-center px-4 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm">
        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
        </svg>
        Назад к списку заказов
    </a>
    
    @php
        // $currentMonth = $startDate->format('F Y'); // Месяц, который отображаем
        $currentMonth = getRussianMonthName($startDate) . ' ' . $startDate->format('Y');
    @endphp
    
    <!-- Включаем компонент информации о клиенте -->
    @include('operator-orders.calendar-components.client-info')

    <!-- Включаем компонент количества поездок -->
    @include('operator-orders.calendar-components.trip-counts')
    

        <!-- Календарь -->
    

    <div class="bg-white shadow overflow-hidden sm:rounded-lg p-2">
        <!-- Добавляем строку с навигацией по месяцам -->
        <div class="flex items-center justify-between mb-0">
            <a href="{{ route('operator.social-taxi.calendar.client', [
                'client' => $client,
                'date' => $prevMonth->format('Y-m-d')
            ] + $urlParams) }}"
               class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-md hover:bg-blue-200 text-sm">
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7" />
                </svg>
                Предыдущий
            </a>
             <div class="text-sm-center font-semibold text-gray-700 mb-0 text-center">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-lg font-bold bg-blue-100 text-blue-800">
                        {{ $currentMonth }}
                    </span>
             </div>
            <a href="{{ route('operator.social-taxi.calendar.client', [
                'client' => $client,
                'date' => $nextMonth->format('Y-m-d')
                ] + $urlParams) }}"
               class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-md hover:bg-blue-200 text-sm">
                Следующий
                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 ml-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7" />
                </svg>
            </a>             
        </div>            

        <div class="grid grid-cols-7 gap-2 mb-0 w-full">
            @foreach(['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'] as $day)
                <div class="text-center text-lg-center font-bold text-gray-500 py-1">
                    {{ $day }}
                </div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 gap-2 w-full">
            @php
                // Определяем день недели первого дня месяца (1 = Пн, 7 = Вс)
                $startDayOfWeek = $startDate->dayOfWeekIso;
                // Вставляем пустые ячейки для дней перед первым днем месяца
                for ($i = 1; $i < $startDayOfWeek; $i++) {
                    echo '<div class="p-2 border border-gray-200 bg-gray-50 text-center text-sm text-gray-400"></div>';
                }

                // Инициализируем $currentDate для цикла по дням месяца
                $currentDate = $startDate->copy();
                $endDateOfMonth = $endDate->copy(); // Копия для сравнения
            @endphp

            @while($currentDate->lte($endDateOfMonth))
                @php
                    $dateKey = $currentDate->format('Y-m-d');
                    $ordersForDay = $calendarData[$dateKey] ?? [];
                    $isToday = $currentDate->format('Y-m-d') === now()->format('Y-m-d');
                    // Определяем количество поездок в этот день 
                    $tripCountForDay = count($ordersForDay);
                @endphp

                <div class="min-h-24 h-auto p-1 border border-gray-200 
                     @if($isToday) bg-blue-50 @else bg-gray-50 @endif 
                ">
                    <div class="text-lg font-medium text-gray-700 mb-1">
                        <span class="inline-flex items-center justify-center w-6 h-6 text-xs font-bold text-gray-800 bg-white rounded-full shadow-sm border border-gray-300">
                            {{ $currentDate->format('j') }}
                         </span>    
                    </div>
                    <div class="space-y-1">
                        @php
                            // Сортировка заказов по времени поездки ---
                            $sortedOrdersForDay = collect($ordersForDay)->sortBy('visit_data')->values(); // превращает массив $ordersForDay в Laravel-коллекцию.
                        @endphp
                        @foreach($sortedOrdersForDay as $order) {{-- Используем отсортированную коллекцию --}}
                            @php
                                // Определяем цвет фона для конкретного заказа ---
                                if(!$order->deleted_at) {
                                    if ($order->type_order == 1) { // Соцтакси
                                        if ($latestOrder && $order->adres_otkuda === $latestOrder->adres_otkuda && $order->adres_kuda === $latestOrder->adres_kuda) {
                                            $orderBgColor = 'bg-green-100'; // Зелёный, если адреса совпадают
                                            $orderTextColor = 'text-green-800';
                                        } else {
                                            $orderBgColor = 'bg-yellow-100'; // Жёлтый, если адреса не совпадают
                                            $orderTextColor = 'text-yellow-800';
                                        }
                                    } else { // Газель (2) или Легковое авто (3)
                                        $orderBgColor = 'bg-gray-100';
                                        $orderTextColor = 'text-gray-800';
                                    }
                                } else {
                                    $orderBgColor = 'bg-red-50';
                                    $orderTextColor = 'text-red-500';
                                }
                            @endphp
                            <div class="text-lg {{ $orderBgColor }} {{ $orderTextColor }} rounded px-1 truncate flex items-center justify-between"
                                 title="Тип: {{ getOrderTypeName($order->type_order) }}. Откуда: {{ $order->adres_otkuda }}. Куда: {{ $order->adres_kuda }}.
                                    @if($order->zena_type == 2) Обратно: {{ $order->adres_obratno }}.@endif
                                    @if($order->deleted_at) Удалён: {{ $order->deleted_at->format('d.m.Y H:i') }}.@endif
                                 ">

                                @if($order->type_order == 1)
                                        <span class="truncate flex-grow">
                                            {{ $order->visit_data->format('H:i') }}
                                        </span>
                                    <!-- Кнопка "Копировать" - отображается только для соцтакси -->
                                    <button
                                        onclick="openCopyModal({{ $order->id }},'{{ $order->visit_data->format('Y-m-d H:i') }}','{{ $order->adres_otkuda }}','{{ $order->adres_kuda }}','{{ $order->predv_way }}' )"
                                        class="ml-1 text-lg text-gray-700 rounded px-1 hover:bg-gray-300"
                                        title="Копировать заказ"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                        </svg>
                                    </button>
                                    
                                    <!-- Кнопка "Множественное копирование" --- -->
                                    <button
                                        type="button"
                                        data-order-id="{{ $order->id }}"
                                        data-visit-datetime="{{ $order->visit_data->format('Y-m-d H:i') }}"
                                        data-adres-otkuda="{{ $order->adres_otkuda }}"
                                        data-adres-kuda="{{ $order->adres_kuda }}"
                                        data-predv-way="{{ $order->predv_way ?? '' }}"
                                        class="copy-multiple-btn ml-1 text-lg text-purple-600 rounded px-1 hover:bg-purple-100"
                                        title="Множественное копирование заказа"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7v8a2 2 0 002 2h6M8 7V5a2 2 0 012-2h4.586a1 1 0 01.707.293l4.414 4.414a1 1 0 01.293.707V15a2 2 0 01-2 2h-2M8 7H6a2 2 0 00-2 2v10a2 2 0 002 2h8a2 2 0 002-2v-2" />
                                        </svg>
                                    </button>
                                    
                                    
                                @else
                                    {{ $order->visit_data->format('H:i') }}
                                @endif
                                <!-- Кнопка "Редактировать" --- -->
                                    <a href="{{ route('social-taxi-orders.edit', [
                                        'social_taxi_order' => $order,
                                        'back_to_operator' => $operatorRoute, // Передаём маршрут оператора
                                        'operator_type' => $operatorCurrentType
                                    ] + $urlParams) }}"
                                       class="text-lg text-yellow-800 rounded px-1 hover:bg-yellow-300"
                                       target ="_blank"
                                       title="Редактировать заказ">
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </a>
                            </div>
                        @endforeach
                    </div>
                </div>

                @php
                    $currentDate->addDay();
                @endphp
            @endwhile

            @php
                // После цикла по дням месяца, определяем день недели последнего дня месяца
                $endDayOfWeek = $endDateOfMonth->dayOfWeekIso; // 1 = Пн, 7 = Вс
                // Вставляем пустые ячейки для дней после последнего дня месяца до конца недели (воскресенья)
                for ($i = $endDayOfWeek + 1; $i <= 7; $i++) {
                    echo '<div class="p-2 border border-gray-200 bg-gray-50 text-center text-sm text-gray-400"></div>';
                }
            @endphp
        </div>
    </div>
        
<!-- Модальное окно с поездками клиента -->
@include('social-taxi-orders.show-components.client-trips-modal')        
<!-- Модальное окно для копирования заказа -->
@include('operator-orders.calendar-components.copy-order-modal')        

<!-- Модальное окно для множественного копирования заказа -->
@include('operator-orders.calendar-components.copy-order-multiple-modal')

    
        <!-- JavaScript для переключения блоков информации -->
    <script>
        function toggleClientInfo() {
            const content = document.getElementById('client-info-content');
            const arrow = document.getElementById('client-info-arrow');
            content.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        }

    </script>
        
        
</div> 
    
<!-- JavaScript для модального окна с поездками клиента-->
    @include('social-taxi-orders.show-components.scripts.modal-scripts')    
<!-- JavaScript для модального окна с копированиес заказов-->
    @include('operator-orders.calendar-components.scripts.modal-scripts')    

   <!-- Передаём даты из PHP в глобальные переменные JavaScript -->
    
    <script>
        window.calendarStartDateFromPHP = "{{ $startDate->format('Y-m-d') }}";
        window.calendarEndDateFromPHP = "{{ $endDate->format('Y-m-d') }}";
        window.copyMultipleApiUrl = "{{ route('api.social-taxi-orders.copy-multiple') }}";
        @include('operator-orders.calendar-components.scripts.multiple-modal-scripts') // Теперь включаем только JS-код
    </script>
</x-app-layout>