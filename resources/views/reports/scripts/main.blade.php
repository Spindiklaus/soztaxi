<script>
// Функции для отображения типа заказа
        function getOrderTypeName(typeId) {
            const types = {
                1: 'Соцтакси',
                2: 'Легковое авто',
                3: 'ГАЗель'
            };
            return types[typeId] || 'Неизвестный тип';
        }

        function getOrderTypeColor(typeId) {
            const colors = {
                1: 'text-blue-600',
                2: 'text-green-600',
                3: 'text-yellow-600'
            };
            return colors[typeId] || 'text-gray-600';
        }

        // --- Новые функции для просмотра заказов по статусу ---

        // Показать модальное окно с заказами по фильтрам статуса
        function showOrdersByStatus(visitDate, typeOrderId, statusOrderId, startDate = null, endDate = null) {
            console.log('showOrdersByStatus called with:', visitDate, typeOrderId, statusOrderId, startDate, endDate);
            const modal = document.getElementById('client-trips-modal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.classList.add('flex');
                // Обновляем заголовок модального окна
                const modalTitle = document.querySelector('#client-trips-modal h3');
                if (modalTitle) {
                    const statusName = getStatusName(statusOrderId);
                    const typeName = typeOrderId ? getOrderTypeName(typeOrderId) : 'Все типы';
                    const dateLabel = visitDate ? formatDate(visitDate) : `${formatDate(startDate)} - ${formatDate(endDate)}`;
                    modalTitle.textContent = `Заказы - ${statusName}, Тип: ${typeName}, Дата: ${dateLabel}`;
                }
            }

            loadOrdersByStatus(visitDate, typeOrderId, statusOrderId, startDate, endDate);
        }

        // Загрузить заказы по фильтрам статуса
        function loadOrdersByStatus(visitDate, typeOrderId, statusOrderId, startDate, endDate) {
            const content = document.getElementById('client-trips-content');
            if (!content) return;

            content.innerHTML = `
                <div class="text-center py-8">
                    <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
                    <p class="mt-2 text-gray-600">Загрузка заказов...</p>
                </div>
            `;

            // Параметры для API-запроса
            const params = new URLSearchParams();
            if (visitDate) params.append('visit_date', visitDate);
            if (typeOrderId) params.append('type_order', typeOrderId);
            params.append('status_order', statusOrderId);
            // Используем диапазон из фильтра, если дата не указана (например, для итогов)
            if (!visitDate && startDate && endDate) {
                params.append('start_date', startDate);
                params.append('end_date', endDate);
            }

            // Замените '/api/orders-by-status-filter' на ваш реальный маршрут API
            fetch(`/api/orders-by-status-filter?${params}`)
                .then(response => {
                    if (!response.ok) {
                        throw new Error(`HTTP error! status: ${response.status}`);
                    }
                    return response.json();
                })
                .then(data => {
                    if (data.error) {
                        throw new Error(data.error);
                    }
                    displayOrdersByStatus(data); // Вызываем функцию для отображения заказов
                })
                .catch(error => {
                    console.error('Ошибка загрузки заказов:', error);
                    if (content) {
                        content.innerHTML = `
                            <div class="text-center py-8">
                                <p class="text-red-600">Ошибка загрузки заказов: ${error.message}</p>
                                <button onclick="closeClientTripsModal()" class="mt-4 px-4 py-2 bg-gray-300 rounded-md">Закрыть</button>
                            </div>
                        `;
                    }
                });
        }

        // Отобразить заказы по фильтрам статуса
        function displayOrdersByStatus(data) {
            const content = document.getElementById('client-trips-content');
            if (!content) return;

            // Ожидаем, что data будет содержать { orders: [...], count: N }
            const { orders, count } = data;

            if (!orders || orders.length === 0) {
                content.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-gray-600">Нет заказов по заданным фильтрам.</p>
                        <button onclick="closeClientTripsModal()" class="mt-4 px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">Закрыть</button>
                    </div>
                `;
                return;
            }

            // Структура таблицы заказов
            let ordersHtml = `
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-gray-50">
                            <tr>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Номер заказа</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата поездки</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Откуда</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Куда</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Обратно</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Клиент</th>
                                <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Текущий статус</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200">
            `;

            orders.forEach(order => {
                // Форматируем дату поездки
                const { date: visitDate, time: visitTime } = parseDateTime(order.visit_data);

                // Форматируем дату обратной поездки
                const { date: obratnoDate, time: obratnoTime } = parseDateTime(order.visit_obratno);

                // Формируем HTML для даты и времени поездки
                let visitDateTimeHtml = `
                    <div class="flex items-center">
                        <span class="inline-flex items-center px-2 py-1 rounded-l text-sm font-medium bg-blue-100 text-blue-800">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                            ${visitDate}
                        </span>
                        <span class="inline-flex items-center px-2 py-1 rounded-r text-sm font-medium bg-blue-50 text-blue-700 border-l border-blue-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            ${visitTime || ''}
                        </span>
                `;

                if (obratnoTime) {
                    visitDateTimeHtml += `
                        -
                        <span class="inline-flex items-center px-2 py-1 rounded-r text-sm font-medium bg-green-50 text-green-700 border-l border-green-200">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                            ${obratnoTime}
                        </span>
                    `;
                }

                visitDateTimeHtml += `</div>`;

                // Формируем HTML для текущего статуса
                let statusHtml = '-';
                if (order.current_status && order.current_status.status_order) {
                    const status = order.current_status.status_order;
                    const colorClass = status.color || 'bg-gray-100 text-gray-800';
                    statusHtml = `
                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}">
                            ${status.name}
                        </span>
                    `;
                }

                ordersHtml += `
                    <tr>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${order.pz_nom || '-'}<br/>
                            <span class="px-4 py-2 whitespace-nowrap text-sm ${getOrderTypeColor(order.type_order)}">${getOrderTypeName(order.type_order)}
                            </span>    
                        </td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${visitDateTimeHtml}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 max-w-xs truncate" title="${order.adres_otkuda || '-'}">${order.adres_otkuda || '-'}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 max-w-xs truncate" title="${order.adres_kuda || '-'}">${order.adres_kuda || '-'}</td>
                        <td class="px-4 py-2 text-sm text-gray-900 max-w-xs truncate" title="${order.adres_obratno || '-'}">${order.adres_obratno || '-'}</td>
                        <td class="px-4 py-2 text-sm text-gray-900">${order.client ? order.client.name : '-'}</td>
                        <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${statusHtml}</td>
                    </tr>
                `;
            });

            ordersHtml += `
                        </tbody>
                    </table>
                </div>
                <div class="mt-4 text-center">
                    <p class="text-sm text-gray-600 mb-2">Всего заказов: ${count}</p>
                    <button onclick="closeClientTripsModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">Закрыть</button>
                </div>
            `;

            content.innerHTML = ordersHtml;
        }

        // --- Вспомогательные функции ---

        // Получить имя статуса по ID
        function getStatusName(statusId) {
            const statusNames = {
                1: 'Принят',
                2: 'Передан в такси',
                3: 'Отменен',
                4: 'Закрыт'
            };
            return statusNames[statusId] || 'Неизвестный статус';
        }

        // Форматировать дату для отображения (YYYY-MM-DD -> DD.MM.YYYY)
        function formatDate(dateStr) {
            if (!dateStr) return '-';
            const [year, month, day] = dateStr.split('-');
            return `${day}.${month}.${year}`;
        }

        function parseDateTime(dateTimeStr) {
            if (!dateTimeStr) return { date: '-', time: '' };

            // Разбиваем по пробелу
            const [dateStr, timeStr] = dateTimeStr.split(' ');

            if (!dateStr || !timeStr) {
                return { date: 'Invalid Date', time: '' };
            }

            // Форматируем дату: YYYY-MM-DD → DD.MM.YYYY
            const formattedDate = dateStr.split('-').reverse().join('.');
            // Берём только HH:MM
            const formattedTime = timeStr.substring(0, 5);

            return { date: formattedDate, time: formattedTime };
        }

        // Закрыть модальное окно
        function closeClientTripsModal() {
            const modal = document.getElementById('client-trips-modal');
            if (modal) {
                modal.classList.add('hidden');
                modal.classList.remove('flex');
            }
        }

        // Закрытие модального окна по клавише Escape
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                closeClientTripsModal();
            }
        });

        // Закрытие модального окна при клике вне его области
        document.addEventListener('click', function(event) {
            const modal = document.getElementById('client-trips-modal');
            if (modal && event.target === modal) {
                closeClientTripsModal();
            }
        });
</script>