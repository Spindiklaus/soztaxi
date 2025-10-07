<!-- trip-history-scripts -->

<script>
// Функция для открытия модального окна с историей адресов
function openAddressHistoryModal() {
    const clientSelect = document.getElementById('client_id');
    
    if (!clientSelect) {
        alert('Элемент выбора клиента не найден');
        return;
    }
    
    if (!clientSelect.value) {
        alert('Сначала выберите клиента');
        return;
    }
    
    const clientId = clientSelect.value;
    
    if (!clientId || clientId === '') {
        alert('Выберите корректного клиента');
        return;
    }
    
    const modal = document.getElementById('address-history-modal');
    
    if (modal) {
        // Используем только Tailwind классы
        modal.classList.remove('hidden');
        // Убедимся, что display не блокирует показ
        modal.style.display = 'block';
        // Загружаем адреса
        loadClientLastTrips(clientId);
    }
}

// Функция для закрытия модального окна
function closeAddressHistoryModal() {
    const modal = document.getElementById('address-history-modal');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = '';
    }
}

// Функция для загрузки последних поездок клиента
function loadClientLastTrips(clientId) {
    const content = document.getElementById('address-history-content');
    if (!content) return;
    
    content.innerHTML = `
        <div class="text-center py-4">
            <div class="animate-spin rounded-full h-8 w-8 border-b-2 border-blue-500 mx-auto"></div>
            <p class="mt-2 text-gray-600">Загрузка адресов...</p>
        </div>
    `;
    
    fetch(`/api/helpers/client-last-trips/${clientId}`)
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
            displayUniqueAddresses(data);
        })
        .catch(error => {
            console.error('Ошибка загрузки адресов:', error);
            content.innerHTML = `
                <div class="text-center py-4">
                    <p class="text-red-600">Ошибка загрузки адресов: ${error.message}</p>
                    <button onclick="closeAddressHistoryModal()" class="mt-4 px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400">Закрыть</button>
                </div>
            `;
        });
}

// Функция для отображения уникальных адресов
function displayUniqueAddresses(trips) {
    const content = document.getElementById('address-history-content');
    if (!content) return;
    
    if (trips.length === 0) {
        content.innerHTML = `
            <div class="text-center py-8">
                <p class="text-gray-600">У клиента нет истории поездок</p>
            </div>
        `;
        return;
    }
    
    // Фильтруем уникальные комбинации адресов
    const uniqueAddresses = [];
    const seenCombinations = [];
    
    trips.forEach(trip => {
        // Создаем уникальный ключ для комбинации адресов
        const key = simpleHash(trip.adres_otkuda + '|' + trip.adres_kuda + '|' + (trip.adres_obratno || ''));
        
        if (!seenCombinations.includes(key)) {
            seenCombinations.push(key);
            uniqueAddresses.push(trip);
        }
    });
    
    // Создаем таблицу
    let html = `
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200">
                <thead class="bg-gray-50">
                    <tr>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Откуда</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Куда</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Обратно</th>
                        <th scope="col" class="px-4 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действие</th>
                    </tr>
                </thead>
                <tbody class="bg-white divide-y divide-gray-200">
    `;
    
    uniqueAddresses.forEach((trip, index) => {
        html += `
            <tr class="hover:bg-gray-50 cursor-pointer" onclick="selectAddressCombination(${index})">
                <td class="px-4 py-3 text-left text-sm text-gray-900 max-w-xs truncate" title="${trip.adres_otkuda || '-'}">
                    ${trip.adres_otkuda || '-'}
                </td>
                <td class="px-4 py-3 text-left text-sm text-gray-900 max-w-xs truncate" title="${trip.adres_kuda || '-'}">
                    ${trip.adres_kuda || '-'}
                </td>
                <td class="px-4 py-3 text-left text-sm text-gray-900 max-w-xs truncate" title="${trip.adres_obratno || '-'}">
                    ${trip.adres_obratno || '-'}
                </td>
                <td class="px-4 py-3 text-sm text-blue-600">
                    Выбрать
                </td>
            </tr>
        `;
        
        // Сохраняем данные адреса в глобальную переменную для последующего использования
        window.addressHistoryData = window.addressHistoryData || [];
        window.addressHistoryData[index] = trip;
    });
    
    html += `
                </tbody>
            </table>
        </div>
    `;
    
    content.innerHTML = html;
}

// Простая функция хэширования для создания уникальных ключей
function simpleHash(string) {
    let hash = 0;
    for (let i = 0; i < string.length; i++) {
        const char = string.charCodeAt(i);
        hash = ((hash << 5) - hash) + char;
        hash = hash & hash; // Преобразуем в 32-битное целое
    }
    return Math.abs(hash).toString();
}

// Функция для выбора комбинации адресов
function selectAddressCombination(index) {
    const addressData = window.addressHistoryData?.[index];
    if (!addressData) return;
    
    // Заполняем поля формы
    const adresOtkudaInput = document.getElementById('adres_otkuda');
    const adresKudaInput = document.getElementById('adres_kuda');
    const adresObratnoInput = document.getElementById('adres_obratno');
    const predvWayInput = document.getElementById('predv_way'); // Для соцтакси
    const typeOrder = {{ $order->type_order }}; // Тип заказа из PHP
    
    if (adresOtkudaInput) adresOtkudaInput.value = addressData.adres_otkuda || '';
    if (adresKudaInput) adresKudaInput.value = addressData.adres_kuda || '';
    if (adresObratnoInput) adresObratnoInput.value = addressData.adres_obratno || '';
    
        // Для соцтакси (type_order = 1) добавляем предварительную дальность, если она есть в данных
    if (typeOrder == 1 && predvWayInput && addressData.predv_way) {
        predvWayInput.value = addressData.predv_way;
    }
    
    // Закрываем модальное окно
    closeAddressHistoryModal();
    
    // Если это соцтакси и есть поле предварительной дальности, запускаем пересчет
    if (typeOrder == 1 && predvWayInput && predvWayInput.value) {
        setTimeout(() => {
            const event = new Event('change');
            predvWayInput.dispatchEvent(event);
        }, 100);
    }
}

// Добавляем обработчики событий после загрузки DOM
document.addEventListener('DOMContentLoaded', function () {
    const openHistoryBtn = document.getElementById('open-address-history-btn');
    
    if (openHistoryBtn) {
        openHistoryBtn.addEventListener('click', openAddressHistoryModal);
    }
    
    // Закрытие модального окна по клавише Escape
    document.addEventListener('keydown', function(event) {
        if (event.key === 'Escape') {
            closeAddressHistoryModal();
        }
    });
    
    // Закрытие модального окна при клике вне его области
    document.addEventListener('click', function(event) {
        const modal = document.getElementById('address-history-modal');
        if (modal && !modal.classList.contains('hidden') && event.target === modal) {
            closeAddressHistoryModal();
        }
    });
});
</script>