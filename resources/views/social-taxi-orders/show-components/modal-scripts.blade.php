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
    const modal = document.getElementById('client-trips-modal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.classList.add('flex');
    }
    
    loadClientTrips(clientId, monthYear);
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
function loadClientTrips(clientId, monthYear) {
    const content = document.getElementById('client-trips-content');
    if (!content) return;
    
    content.innerHTML = `
        <div class="text-center py-8">
            <div class="animate-spin rounded-full h-12 w-12 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-2 text-gray-600">Загрузка поездок...</p>
        </div>
    `;
    
    fetch(`/api/client-trips/${clientId}/${monthYear}`)
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
            displayClientTrips(data);
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

// Отобразить поездки клиента
function displayClientTrips(data) {
    const content = document.getElementById('client-trips-content');
    if (!content) return;
    
    const { trips, clientName, count } = data;
    
    if (trips.length === 0) {
        content.innerHTML = `
            <div class="text-center py-8">
                <p class="text-gray-600">У клиента ${clientName} нет поездок в этом месяце</p>
                <button onclick="closeClientTripsModal()" class="mt-4 px-4 py-2 bg-gray-300 rounded-md">Закрыть</button>
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
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Дата поездки</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Откуда</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Куда</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Тип заказа</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Номер заказа</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
    `;
    
    trips.forEach(trip => {
        tripsHtml += `
            <tr>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                    ${trip.visit_data ? new Date(trip.visit_data).toLocaleDateString('ru-RU') + ' ' + new Date(trip.visit_data).toLocaleTimeString('ru-RU', {hour: '2-digit', minute:'2-digit'}) : '-'}
                </td>
                <td class="px-4 py-2 text-sm text-gray-900 max-w-xs truncate" title="${trip.adres_otkuda || '-'}">${trip.adres_otkuda || '-'}</td>
                <td class="px-4 py-2 text-sm text-gray-900 max-w-xs truncate" title="${trip.adres_kuda || '-'}">${trip.adres_kuda || '-'}</td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">
                    <span class="${getOrderTypeColor(trip.type_order)} font-medium">
                        ${getOrderTypeName(trip.type_order)}
                    </span>
                </td>
                <td class="px-4 py-2 whitespace-nowrap text-sm text-gray-900">${trip.pz_nom || '-'}</td>
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