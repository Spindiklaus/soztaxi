<!-- resources/views/social-taxi-orders/show-components/modal-scripts.blade.php -->
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

// Показать модальное окно с поездками клиента
function showClientTrips(clientId, monthYear) {
    console.log('showClientTrips called with:', clientId, monthYear);
    const modal = document.getElementById('client-trips-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    loadClientTrips(clientId, monthYear, 'normal');
}

// Показать модальное окно с фактическими поездками клиента
function showClientActualTrips(clientId, monthYear) {
    const modal = document.getElementById('client-trips-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    loadClientTrips(clientId, monthYear, 'actual');
}

// Показать модальное окно с поездками, переданными в такси
function showClientTaxiSentTrips(clientId, monthYear) {
    const modal = document.getElementById('client-trips-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    loadClientTrips(clientId, monthYear, 'taxi-sent');
}

// Закрыть модальное окно
function closeClientTripsModal() {
    const modal = document.getElementById('client-trips-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.classList.remove('flex');
    }
}

// Загрузить поездки клиента
function loadClientTrips(clientId, monthYear, type = 'normal') {
    const content = document.getElementById('client-trips-content');
    if (!content) return;
    
    content.innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-2 text-gray-600">Загрузка поездок...</p>
        </div>
    `;
    
    // Выбираем правильный маршрут в зависимости от типа
    let apiUrl;
    switch(type) {
        case 'actual':
            apiUrl = `/api/client-actual-trips/${clientId}/${monthYear}`;
            break;
        case 'taxi-sent':
            apiUrl = `/api/client-taxi-sent-trips/${clientId}/${monthYear}`;
            break;
        default:
            apiUrl = `/api/client-trips/${clientId}/${monthYear}`;
    }
    
    fetch(apiUrl)
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
            displayClientTrips(data, type);
        })
        .catch(error => {
            console.error('Ошибка загрузки поездок:', error);
            if (content) {
                content.innerHTML = `
                    <div class="text-center py-8">
                        <p class="text-red-600">Ошибка загрузки поездок: ${error.message}</p>
                        <button onclick="closeClientTripsModal()" class="mt-4 px-4 py-2 bg-gray-300 rounded-md">Закрыть</button>
                    </div>
                `;
            }
        });
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

// Отобразить поездки клиента
function displayClientTrips(data, type = 'normal') {
    const content = document.getElementById('client-trips-content');
    if (!content) return;

    const { trips, clientName, count, period } = data;

    // Обновляем заголовок модального окна в зависимости от типа
    const modalTitle = document.querySelector('#client-trips-modal h3');
    if (modalTitle) {
        switch(type) {
            case 'actual':
                modalTitle.textContent = `Фактические (закрытые) поездки клиента за ${period}`;
                break;
            case 'taxi-sent':
                modalTitle.textContent = `Поездки клиента за ${period}, переданные в такси `;
                break;
            default:
                modalTitle.textContent = `Поездки клиента за ${period}`;
        }
    }

    if (trips.length === 0) {
        let message = '';
        switch(type) {
            case 'actual':
                message = 'У клиента нет фактических поездок';
                break;
            case 'taxi-sent':
                message = 'У клиента нет поездок, переданных в такси';
                break;
            default:
                message = 'У клиента нет поездок';
        }

        content.innerHTML = `
            <div class="text-center py-8">
                <p class="text-gray-600">${message} в ${period}</p>
                <button onclick="closeClientTripsModal()" class="mt-4 px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">Закрыть</button>
            </div>
        `;
        return;
    }

    let tripsHtml = `
        <h4 class="text-md font-semibold text-gray-700 mb-4 px-4">Клиент: ${clientName}</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип /Номер заказа</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата поездки</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Откуда</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Куда</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Обратно</th>
    `;

    // Добавляем дополнительные колонки в зависимости от типа
    switch(type) {
        case 'actual':
            tripsHtml += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата закрытия</th>';
            break;
        case 'taxi-sent':
            tripsHtml += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата передачи в такси</th>';
            break;
    }

    // Добавляем колонку текущего статуса
    tripsHtml += '<th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Текущий статус</th>';

    tripsHtml += `
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
    `;

    trips.forEach((trip, index) => {
        
        // Проверяем, удален ли заказ
        const isDeleted = trip.deleted_at !== null;
        const rowClass = isDeleted ? 'bg-red-50' : '';
        
        // Форматируем дату поездки
        const { date: visitDate, time: visitTime } = parseDateTime(trip.visit_data);

        // Форматируем дату обратной поездки
        const { date: obratnoDate, time: obratnoTime } = parseDateTime(trip.visit_obratno);

        // Создаём HTML для отображения даты и времени, как в show
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
                    ${visitTime}
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

        tripsHtml += `
            <tr class="${rowClass}">
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                    <div class="${getOrderTypeColor(trip.type_order)} font-medium">
                        ${getOrderTypeName(trip.type_order)}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        ${trip.pz_nom || '-'}
                    </div>
                     ${isDeleted ? `
                        <div class="text-xs text-red-600 font-medium mt-1">
                            УДАЛЕН: ${parseDateTime(trip.deleted_at).date} ${parseDateTime(trip.deleted_at).time}
                        </div>
                    ` : ''}
                </td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                    ${visitDateTimeHtml}
                </td>
                <td class="px-4 py-2 text-sm text-gray-900 max-w-xs truncate" title="${trip.adres_otkuda || '-'}">${trip.adres_otkuda || '-'}</td>
                <td class="px-4 py-2 text-sm text-gray-900 max-w-xs truncate" title="${trip.adres_kuda || '-'}">${trip.adres_kuda || '-'}</td>
                <td class="px-4 py-2 text-sm text-gray-900 max-w-xs truncate" title="${trip.adres_obratno || '-'}">${trip.adres_obratno || '-'}</td>
        `;

        // Добавляем дополнительные данные в зависимости от типа
        switch(type) {
            case 'actual':
                const { date: closedDate, time: closedTime } = parseDateTime(trip.closed_at);
                tripsHtml += `
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                        ${closedDate}
                        ${closedTime ? `<br><span class="text-xs text-gray-500">${closedTime}</span>` : ''}
                    </td>
                `;
                break;
            case 'taxi-sent':
                const { date: sentDate, time: sentTime } = parseDateTime(trip.taxi_sent_at);
                tripsHtml += `
                    <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                        ${sentDate}
                        ${sentTime ? `<br><span class="text-xs text-gray-500">${sentTime}</span>` : ''}
                    </td>
                `;
                break;
        }

        // Добавляем текущий статус заказа
        let statusHtml = '-';
        if (trip.current_status && trip.current_status.status_order) {
            const status = trip.current_status.status_order;
            const colorClass = status.color || 'bg-gray-100 text-gray-800';
            statusHtml = `
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium ${colorClass}">
                    ${status.name}
                </span>
            `;
        }

        tripsHtml += `
            <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                ${statusHtml}
            </td>
        `;

        tripsHtml += `
            </tr>
        `;
    });

    tripsHtml += `
                </tbody>
            </table>
        </div>
        <div class="mt-4 text-center mb-4">
            <p class="text-sm text-gray-600 mb-2">Всего поездок: ${count}</p>
            <button onclick="closeClientTripsModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">Закрыть</button>
        </div>
    `;

    content.innerHTML = tripsHtml;
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