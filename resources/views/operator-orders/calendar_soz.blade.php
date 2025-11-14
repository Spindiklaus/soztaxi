<x-app-layout>
 <div class="max-w-3xl mx-auto px-4 py-6">
    <h1 class="text-2xl font-bold text-gray-800 mb-2">Календарь поездок клиента: {{ $client->fio }}</h1>

    <!-- Кнопка "Назад" -->
    <a href="{{ route($operatorRoute) . '?' . http_build_query($urlParams) }}"
       class="mb-4 inline-flex items-center px-4 py-1 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm">
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
    

    <div class="bg-white shadow overflow-hidden sm:rounded-lg p-4">
        <h2 class="text-xl font-semibold text-gray-700 mb-4 text-center">{{ $currentMonth }}. Календарь</h2>

        <div class="grid grid-cols-7 gap-1 mb-2 w-full">
            @foreach(['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'] as $day)
                <div class="text-center text-xs font-bold text-gray-500 py-1">{{ $day }}</div>
            @endforeach
        </div>

        <div class="grid grid-cols-7 gap-1 w-full">
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
                @endphp

                <div class="min-h-24 p-1 border border-gray-200 @if($isToday) bg-blue-50 @endif">
                    <div class="text-xs font-medium text-gray-700 mb-1">{{ $currentDate->format('j') }}</div>
                    <div class="space-y-1 max-h-20 overflow-y-auto">
                        @foreach($ordersForDay as $order)
                            <div class="text-xs bg-blue-100 text-blue-800 rounded px-1 truncate" title="{{ $order->adres_otkuda }} -> {{ $order->adres_kuda }} ({{ $order->visit_data->format('H:i') }})">
                                {{ $order->visit_data->format('H:i') }}
                            </div>
                            <!-- Кнопка "Копировать" -->
                                <button
                                    onclick="openCopyModal({{ $order->id }}, '{{ $order->visit_data->format('Y-m-d H:i') }}', '{{ $order->adres_otkuda }}', '{{ $order->adres_kuda }}', '{{ $order->adres_obratno }}', '{{ $order->zena_type }}')"
                                    class="ml-1 text-xs bg-gray-200 text-gray-700 rounded px-1 hover:bg-gray-300"
                                    title="Копировать заказ"
                                >
                                    К
                                </button>
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
    
        <!-- JavaScript для переключения блоков информации -->
    <script>
        function toggleClientInfo() {
            const content = document.getElementById('client-info-content');
            const arrow = document.getElementById('client-info-arrow');
            content.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        }

        function toggleTripCounts() {
            const content = document.getElementById('trip-counts-content');
            const arrow = document.getElementById('trip-counts-arrow');
            content.classList.toggle('hidden');
            arrow.classList.toggle('rotate-180');
        }
    </script>
        
        
</div> 
    
<!-- JavaScript для модального окна с поездками клиента-->
    @include('social-taxi-orders.show-components.scripts.modal-scripts')    
<!-- JavaScript для модального окна с копированиес заказов-->
    @include('operator-orders.calendar-components.scripts.modal-scripts')    
</x-app-layout>