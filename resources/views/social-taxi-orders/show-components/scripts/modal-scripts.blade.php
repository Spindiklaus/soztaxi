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
    // Добавим отладку для проверки данных
    console.log('Trip data:', trips);
    
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
        <h4 class="text-md font-semibold text-gray-700 mb-4">Клиент: ${clientName}</h4>
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип заказа<br>Номер заказа</th>
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
            // УБРАЛИ колонку "Оператор такси"
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
        // Добавим отладку для каждой поездки
        console.log(`Trip ${index}:`, trip);
        // Форматируем дату поездки
        const { date: visitDate, time: visitTime } = parseDateTime(trip.visit_data);
        
        // Форматируем дату обратной поездки
        let visitObratnoInfo = '';
        console.log(`Trip ${index} visit_obratno:`, trip.visit_obratno);
        
        if (trip.visit_obratno) {
            const { date, time } = parseDateTime(trip.visit_obratno);
            visitObratnoInfo = `<span class="text-xs text-blue-600">Обратно: ${time}</span>`;
        }
        
        tripsHtml += `
            <tr>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                    <div class="${getOrderTypeColor(trip.type_order)} font-medium">
                        ${getOrderTypeName(trip.type_order)}
                    </div>
                    <div class="text-xs text-gray-500 mt-1">
                        ${trip.pz_nom || '-'}
                    </div>
                </td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                    ${visitDate}
                    ${visitTime ? `<br><span class="text-xs text-gray-900">${visitTime}</span>` : ''}
                    ${visitObratnoInfo}
                </td>
                <td class="px-4 py-2 text-sm text-gray-900 max-w-xs truncate" title="${trip.adres_otkuda || '-'}">${trip.adres_otkuda || '-'}</td>
                <td class="px-4 py-2 text-sm text-gray-900 max-w-xs truncate" title="${trip.adres_kuda || '-'}">${trip.adres_kuda || '-'}</td>
                <td class="px-4 py-2 text-sm text-gray-900 max-w-xs truncate" title="${trip.adres_obratno || '-'}">${trip.adres_obratno || '-'}</td>
        `;
        
        // Добавляем дополнительные данные в зависимости от типа
        switch(type) {
            case 'actual':
                let closedDate = '-';
                let closedTime = '';
                if (trip.closed_at) {
                    const closedObj = new Date(trip.closed_at);
                    closedDate = closedObj.toLocaleDateString('ru-RU');
                    closedTime = closedObj.toLocaleTimeString('ru-RU', {hour: '2-digit', minute:'2-digit'});
                }
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
                // УБРАЛИ вывод оператора такси
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
        <div class="mt-4 text-center">
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