<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <div class="bg-gray-100 py-2">
        <div class="max-w-[1536px] mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Заголовок -->
            <div class="flex justify-between items-center mb-2">
                <h1 class="text-2xl font-bold text-gray-800">Сверка файла Excel из такси с базой данных</h1>

                <!-- Кнопка "Назад" -->
                <a href="{{ route('taxi_sent-orders.index', $request->only(['date_from', 'date_to', 'taxi_id'])) }}"
                   class="inline-flex items-center px-4 py-2 bg-gray-300 text-gray-800 rounded-md hover:bg-gray-400">
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                    </svg>
                    Назад к списку
                </a>
            </div>

            <!-- Информация о фильтрах -->
<!--            <div class="mb-4 p-3 bg-white rounded shadow">
                <p><strong>Период:</strong> {{ date('d.m.Y', strtotime($request->date_from)) }} - {{ date('d.m.Y', strtotime($request->date_to)) }}</p>
            </div>-->
            
            <!-- Не найденные заказы -->
            @if(count($notFound) > 0)
                <div class="max-w-[768px] mx-auto">
                    <h2 class="text-lg font-semibold mb-2 text-red-600">
                        Заказы из такси, не найденные в базе ({{ count($notFound) }})
                    </h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border-collapse">
                            <thead>
                                <tr class="bg-red-100">
                                    <th class="px-4 py-2 border">№ заказа (из файла)</th>
                                    <th class="px-4 py-2 border">Предв. дальность (из файла)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($notFound as $item)
                                    <tr class="bg-red-50">
                                        <td class="px-4 py-2 border text-center">{{ $item['pz_nom'] }}</td>
                                        <td class="px-4 py-2 border text-center">{{ $item['file_predv_way'] }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
            
            

            <!-- Результаты сверки -->
            @if(count($results) > 0)
                <div class="mb-6">
                    <h2 class="text-xl font-semibold mb-2">Найденные в файле заказы (всего {{ count($results) }})</h2>
                    <div class="overflow-x-auto">
                        <table class="min-w-full bg-white border-collapse">
                            <thead>
                                <tr class="bg-gray-200">
                                    <th class="px-4 py-2 border">№ заказа</th>
                                    <th class="px-4 py-2 border">Предв. дальность (из файла)</th>
                                    <th class="px-4 py-2 border">Цена за поездку (из файла)</th>
                                    <th class="px-4 py-2 border">Сумма к оплате (из файла)</th>
                                    <th class="px-4 py-2 border">Сумма к возмещению (из файла)</th>
                                    <th class="px-4 py-2 border">Предв. дальность (в БД)</th>
                                    <th class="px-4 py-2 border">Цена за поездку (из БД)</th>
                                    <th class="px-4 py-2 border">Сумма к оплате (из БД)</th>
                                    <th class="px-4 py-2 border">Сумма к возмещению (из БД)</th>
                                    <th class="px-4 py-2 border">Действия</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($results as $result)
                                    @php
                                        //<!-- Подсветка для Предв. дальности -->
                                        $predvWayClass = ((float) $result['file_predv_way'] != 0 || (float) $result['db_predv_way'] != 0) && (float) $result['file_predv_way'] != (float) $result['db_predv_way'] ? 'bg-red-200' : '';
                                        //<!-- Подсветка для Цена за поездку -->
                                        $priceClass = ((float) $result['file_price'] != 0 || (float) $result['db_price'] != 0) && (float) $result['file_price'] != (float) $result['db_price'] ? 'bg-red-200' : '';
                                        //<!-- Подсветка для Сумма к оплате -->
                                        $sumToPayClass = ((float) $result['file_sum_to_pay'] != 0 || (float) $result['db_sum_to_pay'] != 0) && (float) $result['file_sum_to_pay'] != (float) $result['db_sum_to_pay'] ? 'bg-red-200' : '';
                                        //<!-- Подсветка для Сумма к возмещению -->
                                        $sumToReimburseClass = ((float) $result['file_sum_to_reimburse'] != 0 || (float) $result['db_sum_to_reimburse'] != 0) && (float) $result['file_sum_to_reimburse'] != (float) $result['db_sum_to_reimburse'] ? 'bg-red-200' : '';
                                    @endphp                                        
                                    <tr>
                                        <td class="px-4 py-2 border {{ $result['status_color'] }}" title="{{ $result['status_name'] }}">
                                            {{ $result['pz_nom'] }}
                                        </td>
                                        
                                        <td class="px-4 py-2 border {{ $predvWayClass }}">
                                            <span id="copy-{{ $result['order_id'] }}-file">
                                                {{ (float) $result['file_predv_way'] != 0 ? $result['file_predv_way'] : '' }}
                                            </span>
                                            @if ($predvWayClass)
                                                <!-- Форма для обновления predv_way -->
<!--                                                <form method="POST" action="{{-- route('taxi-orders.update-predv-way') --}}" style="display:inline;" onsubmit="return confirm('Вы уверены, что хотите обновить предварительную дальность для этого заказа?')">
                                                    @csrf
                                                    <input type="hidden" name="order_id" value="{{ $result['order_id'] }}">
                                                    <input type="hidden" name="new_predv_way" value="{{ $result['file_predv_way'] }}">
                                                     Передаём параметры фильтрации, чтобы вернуться туда же 
                                                    <input type="hidden" name="date_from" value="{{ $request->date_from }}">
                                                    <input type="hidden" name="date_to" value="{{ $request->date_to }}">
                                                    <button type="submit" class="ml-2 text-xs bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded">
                                                        Обновить
                                                    </button>
                                                </form>-->
                                                <button type="button"
                                                        onclick="updatePredvWayAjax({{ $result['order_id'] }}, '{{ $result['file_predv_way'] }}', this)"
                                                        class="ml-2 text-xs bg-blue-500 hover:bg-blue-600 text-white px-2 py-1 rounded"
                                                        title="Обновить предварительную дальность">
                                                    Обновить
                                                </button>
                                            @endif
                                        </td>
                                        <td class="px-4 py-2 border {{ $priceClass }}">{{ (float) $result['file_price'] != 0 ? $result['file_price'] : '' }}</td>
                                        <td class="px-4 py-2 border {{ $sumToPayClass }}">{{ (float) $result['file_sum_to_pay'] != 0 ? $result['file_sum_to_pay'] : '' }}</td>
                                        <td class="px-4 py-2 border {{ $sumToReimburseClass }}">{{ (float) $result['file_sum_to_reimburse'] != 0 ? $result['file_sum_to_reimburse'] : '' }}</td>
                                        <!-- Подсветка для Предв. дальности в БД (вторая ячейка из пары) -->
                                        <td class="px-4 py-2 border {{ $predvWayClass }}">{{ (float) $result['db_predv_way'] != 0 ? $result['db_predv_way'] : '' }}</td>
                                        <!-- Подсветка для Цена за поездку в БД (вторая ячейка из пары) -->
                                        <td class="px-4 py-2 border {{ $priceClass }}">{{ (float) $result['db_price'] != 0 ? $result['db_price'] : '' }}</td>
                                        <!-- Подсветка для Сумма к оплате в БД (вторая ячейка из пары) -->
                                        <td class="px-4 py-2 border {{ $sumToPayClass }}">{{ (float) $result['db_sum_to_pay'] != 0 ? $result['db_sum_to_pay'] : '' }}</td>
                                        <!-- Подсветка для Сумма к возмещению в БД (вторая ячейка из пары) -->
                                        <td class="px-4 py-2 border {{ $sumToReimburseClass }}">{{ (float) $result['db_sum_to_reimburse'] != 0 ? $result['db_sum_to_reimburse'] : '' }}</td>
                                        <td class="px-4 py-2 border">
                                            <a href="{{ route('social-taxi-orders.show', $result['order_id']) }}" title="Просмотреть заказ" target="_blank"
                                               class="text-blue-600 hover:text-blue-800 text-sm">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                                </svg>
                                            </a>
                                        </td>
                                    </tr>
                                @endforeach
                                @php
                                    // Подсветка для итогов
                                    $totalPredvWayClass = ($summary['file_predv_way'] != 0 || $summary['db_predv_way'] != 0) && $summary['file_predv_way'] != $summary['db_predv_way'] ? 'bg-red-200' : '';
                                    $totalPriceClass = ($summary['file_price'] != 0 || $summary['db_price'] != 0) && $summary['file_price'] != $summary['db_price'] ? 'bg-red-200' : '';
                                    $totalSumToPayClass = ($summary['file_sum_to_pay'] != 0 || $summary['db_sum_to_pay'] != 0) && $summary['file_sum_to_pay'] != $summary['db_sum_to_pay'] ? 'bg-red-200' : '';
                                    $totalSumToReimburseClass = ($summary['file_sum_to_reimburse'] != 0 || $summary['db_sum_to_reimburse'] != 0) && $summary['file_sum_to_reimburse'] != $summary['db_sum_to_reimburse'] ? 'bg-red-200' : '';
                                @endphp
                                <!-- Строка с итогами -->
                                <tr class="bg-gray-300 font-semibold">
                                    <td class="px-4 py-2 border text-right">Итого:</td>
                                    <td class="px-4 py-2 border {{ $totalPredvWayClass }}">{{ number_format($summary['file_predv_way'], 2, '.', '') }}</td>
                                    <td class="px-4 py-2 border {{ $totalPriceClass }}">{{ number_format($summary['file_price'], 2, '.', '') }}</td>
                                    <td class="px-4 py-2 border {{ $totalSumToPayClass }}">{{ number_format($summary['file_sum_to_pay'], 2, '.', '') }}</td>
                                    <td class="px-4 py-2 border {{ $totalSumToReimburseClass }}">{{ number_format($summary['file_sum_to_reimburse'], 2, '.', '') }}</td>
                                    <td class="px-4 py-2 border {{ $totalPredvWayClass }}">{{ number_format($summary['db_predv_way'], 2, '.', '') }}</td>
                                    <td class="px-4 py-2 border {{ $totalPriceClass }}">{{ number_format($summary['db_price'], 2, '.', '') }}</td>
                                    <td class="px-4 py-2 border {{ $totalSumToPayClass }}">{{ number_format($summary['db_sum_to_pay'], 2, '.', '') }}</td>
                                    <td class="px-4 py-2 border {{ $totalSumToReimburseClass }}">{{ number_format($summary['db_sum_to_reimburse'], 2, '.', '') }}</td>
                                    <td class="px-4 py-2 border"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif

            

        </div>
    </div>

    <script>
        // Убедитесь, что CSRF-токен доступен
        const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');

        function updatePredvWayAjax(orderId, newPredvWayValue, buttonElement) {
            // Проверяем подтверждение
            if (!confirm('Вы уверены, что хотите обновить предварительную дальность для этого заказа?')) {
                return;
            }

            // Блокируем кнопку на время запроса
            const originalButtonText = buttonElement.textContent;
            buttonElement.disabled = true;
            buttonElement.textContent = '...';

            fetch('{{ route('taxi-orders.update-predv-way-ajax') }}', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken,
                },
                body: JSON.stringify({
                    order_id: orderId,
                    new_predv_way: newPredvWayValue,
                    // Не нужно передавать date_from, date_to, taxi_id, так как это AJAX и мы не делаем редирект
                })
            })
            .then(response => {
                // Проверяем, успешен ли HTTP-ответ
                if (!response.ok) {
                    // Если сервер вернул ошибку (например, 500), бросаем исключение
                    return response.json().then(errData => {
                        // Пытаемся получить сообщение об ошибке из JSON
                        throw new Error(errData.message || 'Ошибка сервера');
                    });
                }
                // Если HTTP-ответ успешен, парсим JSON
                return response.json();
            })
            .then(data => {
                if (data.success) {
                    // Показываем сообщение об успехе
                    alert(data.message);

                    // --- Обновление UI ---
                    // Найти span с копируемым значением и обновить его
                    const copySpan = document.getElementById(`copy-${orderId}-file`);
                    if (copySpan) {
                        copySpan.textContent = data.new_predv_way;
                    }

                    // Найти ячейку с DB значением и обновить её
                    // Предположим, что ячейка с DB значением находится в той же строке, и у неё есть класс, указывающий на order_id
                    // Найдём строку (tr), в которой находится кнопка
                    const row = buttonElement.closest('tr');
                    if (row) {
                        // Найдём ячейку, которая содержит DB predv_way (это 6-я ячейка в строке, индекс 5, если считать с 0)
                        const dbPredvWayCell = row.cells[5]; // Индекс 5 соответствует 'Предв. дальность (в БД)'
                        if (dbPredvWayCell) {
                            // Обновим текст ячейки
                            dbPredvWayCell.textContent = data.new_predv_way;
                            // Уберём подсветку, так как значения теперь равны
                            dbPredvWayCell.classList.remove('bg-red-200');
                            // Найдём соответствующую ячейку с файловым значением в той же строке (индекс 1)
                            const filePredvWayCell = row.cells[1];
                            if (filePredvWayCell) {
                                 // Уберём подсветку и кнопку из ячейки файла
                                 filePredvWayCell.classList.remove('bg-red-200');
                                 // Удалим кнопку из DOM
                                 const buttonInCell = filePredvWayCell.querySelector('button');
                                 if (buttonInCell) {
                                     buttonInCell.remove();
                                 }
                            }
                        }
                    }

                } else {
                    // Если success = false, показываем сообщение из ответа
                    alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                // Обработка ошибок сети или других исключений
                console.error('Error:', error);
                alert('Произошла ошибка при обновлении: ' + error.message);
            })
            .finally(() => {
                // Восстанавливаем кнопку
                buttonElement.disabled = false;
                buttonElement.textContent = originalButtonText;
            });
        }
    </script>
</x-app-layout>