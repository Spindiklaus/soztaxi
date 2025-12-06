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
                                    <th class="px-4 py-2 border">№ заказа (из Excel)</th>
                                    <th class="px-4 py-2 border">Предв. дальность (из Excel)</th>
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
                                    <th class="px-4 py-2 border">Предв. дальность (из Excel)</th>
                                    <th class="px-4 py-2 border">Цена за поездку (из Excel)</th>
                                    <th class="px-4 py-2 border">Сумма к оплате (из Excel)</th>
                                    <th class="px-4 py-2 border">Сумма к возмещению (из Excel)</th>
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
                                                    <!-- Иконка "циклическая стрелка" (refresh) -->
                                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                    </svg>
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
            })
        })
        .then(response => {
            if (!response.ok) {
                return response.json().then(errData => {
                    throw new Error(errData.message || 'Ошибка сервера');
                });
            }
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

                // Найти строку (tr), в которой находится кнопка
                const row = buttonElement.closest('tr');
                if (row) {
                    // Найдём ячейку, которая содержит СТАРОЕ DB predv_way (это 6-я ячейка в строке, индекс 5, если считать с 0)
                    const dbPredvWayCell = row.cells[5]; // Индекс 5 соответствует 'Предв. дальность (в БД)'
                    if (dbPredvWayCell) {
                        // Запомним СТАРОЕ значение DB predv_way до его изменения в UI
                        const oldDbPredvWayValue = parseFloat(dbPredvWayCell.textContent) || 0;

                        // Обновим текст ячейки DB predv_way на новое значение
                        dbPredvWayCell.textContent = data.new_predv_way;

                        // Уберём подсветку, так как значения (file и db) теперь равны (или db равно новому file)
                        // Подсветка зависит от несовпадения file и db. После обновления db = file, подсветка убирается.
                        dbPredvWayCell.classList.remove('bg-red-200');

                        // Найдём соответствующую ячейку с файловым значением в той же строке (индекс 1)
                        const filePredvWayCell = row.cells[1];
                        if (filePredvWayCell) {
                             // Уберём подсветку из ячейки файла
                             filePredvWayCell.classList.remove('bg-red-200');
                             // Удалим кнопку из DOM
                             const buttonInCell = filePredvWayCell.querySelector('button');
                             if (buttonInCell) {
                                 buttonInCell.remove();
                             }
                        }

                        // --- Пересчитываем и обновляем итоги ---
                        updateTotals(oldDbPredvWayValue, parseFloat(data.new_predv_way));
                    }
                }
            } else {
                alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Произошла ошибка при обновлении: ' + error.message);
        })
        .finally(() => {
            buttonElement.disabled = false;
            buttonElement.textContent = originalButtonText;
        });
    }

    // --- Новая функция для обновления итогов ---
    function updateTotals(oldValue, newValue) {
        // Найдём строку с итогами (предполагаем, что она одна и имеет font-semibold)
        const totalRow = document.querySelector('tbody tr.bg-gray-300.font-semibold');
        if (!totalRow) {
            console.error('Total row not found');
            return;
        }

        // Ячейки итогов (предполагаем фиксированный порядок, как в шаблоне)
        // Индексы: 0-номер, 1-file_predv, 2-file_price, 3-file_sum_pay, 4-file_sum_reim, 5-db_predv, 6-db_price, 7-db_sum_pay, 8-db_sum_reim, 9-действия
        const totalDbPredvWayCell = totalRow.cells[5]; // 'Предв. дальность (в БД)'
        const totalFilePredvWayCell = totalRow.cells[1]; // 'Предв. дальность (из Excel)'

        if (totalDbPredvWayCell && totalFilePredvWayCell) {
            // Получаем текущие отображаемые итоговые значения
            let currentTotalDb = parseFloat(totalDbPredvWayCell.textContent) || 0;
            let currentTotalFile = parseFloat(totalFilePredvWayCell.textContent) || 0;

            // Пересчитываем итог для DB predv_way
            let newTotalDb = currentTotalDb - oldValue + newValue;

            // Пересчитываем итог для FILE predv_way (это не меняется при обновлении DB, но если бы мы обновляли и файловое значение, то тут был бы пересчёт)
            // В данном случае, файловое значение итога не изменяется при обновлении DB predv_way, так что newTotalFile = currentTotalFile
            let newTotalFile = currentTotalFile;

            // Форматируем и обновляем текст ячеек
            totalDbPredvWayCell.textContent = newTotalDb.toFixed(2);
            // totalFilePredvWayCell.textContent = newTotalFile.toFixed(2); // Не нужно, если файл не меняется

            // --- Пересчитываем подсветку итоговой строки для predv_way ---
            const totalMismatch = (newTotalFile !== 0 || newTotalDb !== 0) && newTotalFile !== newTotalDb;
            const totalPredvWayClass = 'bg-red-200';

            if (totalMismatch) {
                totalDbPredvWayCell.classList.add(totalPredvWayClass);
                totalFilePredvWayCell.classList.add(totalPredvWayClass);
            } else {
                totalDbPredvWayCell.classList.remove(totalPredvWayClass);
                totalFilePredvWayCell.classList.remove(totalPredvWayClass);
            }
        } else {
            console.error('Total predv_way cells not found');
        }

        // Здесь можно добавить пересчёт и для других итогов (цена, оплата, возмещение),
        // если бы они тоже обновлялись через AJAX. В текущем сценарии обновляется только predv_way.
    }
    </script>
</x-app-layout>