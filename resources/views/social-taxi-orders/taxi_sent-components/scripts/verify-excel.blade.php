{{-- resources/views/social-taxi-orders/taxi_sent-components/scripts/verify-excel.blade.php --}}
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