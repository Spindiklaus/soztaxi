<!-- trip-info.blade.php -->
<script>
// Функции работы с информацией о поездках
function updateClientTripsInfo(clientId, visitDate) {
    if (!clientId || !visitDate) {
        hideClientTripsInfo();
        return;
    }
    
    const monthYear = visitDate.substring(0, 7); // YYYY-MM формат
    const clientTripsInfo = document.getElementById('client-trips-info');
    const clientTripsButton = document.getElementById('client-trips-button');
    const clientFreeTripsButton = document.getElementById('client-free-trips-button'); // со 100% скидкой
    const clientActualTripsButton = document.getElementById('client-actual-trips-button');
    const clientTaxiSentTripsButton = document.getElementById('client-taxi-sent-trips-button');
    
    if (clientTripsInfo) {
        clientTripsInfo.style.display = 'block';
    }
    
    // Обновляем onclick атрибуты кнопок
    if (clientTripsButton) {
        clientTripsButton.onclick = () => showClientTrips(clientId, monthYear);
        clientTripsButton.innerHTML = '<span class="loading">Загрузка...</span>';
    }
    
    if (clientFreeTripsButton) {
        clientFreeTripsButton.onclick = () => showClientFreeTrips(clientId, monthYear);
        clientFreeTripsButton.innerHTML = '<span class="loading">Загрузка...</span>';
    }
    
    if (clientActualTripsButton) {
        clientActualTripsButton.onclick = () => showClientActualTrips(clientId, monthYear);
        clientActualTripsButton.innerHTML = '<span class="loading">Загрузка...</span>';
    }
    
    if (clientTaxiSentTripsButton) {
        clientTaxiSentTripsButton.onclick = () => showClientTaxiSentTrips(clientId, monthYear);
        clientTaxiSentTripsButton.innerHTML = '<span class="loading">Загрузка...</span>';
    }
    
    // Получаем данные о поездках
    Promise.all([
        fetchClientTripsCount(clientId, monthYear),
        fetchClientFreeTripsCount(clientId, monthYear),
        fetchClientActualTripsCount(clientId, monthYear),
        fetchClientTaxiSentTripsCount(clientId, monthYear)
    ])
    .then(([tripsCount, freeTripsCount, actualTripsCount, taxiSentTripsCount]) => {
        if (clientTripsButton) {
            clientTripsButton.innerHTML = tripsCount;
        }
        if (clientFreeTripsButton) {
            clientFreeTripsButton.innerHTML = freeTripsCount;
        }
        if (clientActualTripsButton) {
            clientActualTripsButton.innerHTML = actualTripsCount;
        }
        if (clientTaxiSentTripsButton) {
            clientTaxiSentTripsButton.innerHTML = taxiSentTripsCount;
        }
    })
    .catch(error => {
        console.error('Ошибка при получении данных о поездках:', error);
        if (clientTripsButton) {
            clientTripsButton.innerHTML = '0';
        }
        if (clientFreeTripsButton) {
            clientFreeTripsButton.innerHTML = '0';
        }
        if (clientActualTripsButton) {
            clientActualTripsButton.innerHTML = '0';
        }
        if (clientTaxiSentTripsButton) {
            clientTaxiSentTripsButton.innerHTML = '0';
        }
    });
}

// Функции для получения данных поездок
function fetchClientTripsCount(clientId, monthYear) {
    return fetch(`/api/client-trips/${clientId}/${monthYear}`)
        .then(response => response.json())
        .then(data => data.count || 0)
        .catch(() => 0);
}

function fetchClientFreeTripsCount(clientId, monthYear) {
    return fetch(`/api/client-free-trips/${clientId}/${monthYear}`) 
        .then(response => response.json())
        .then(data => data.freeCount || 0) // Используем freeCount из ответа
        .catch(() => 0);
}

function fetchClientActualTripsCount(clientId, monthYear) {
    return fetch(`/api/client-actual-trips/${clientId}/${monthYear}`)
        .then(response => response.json())
        .then(data => data.count || 0)
        .catch(() => 0);
}

function fetchClientTaxiSentTripsCount(clientId, monthYear) {
    return fetch(`/api/client-taxi-sent-trips/${clientId}/${monthYear}`)
        .then(response => response.json())
        .then(data => data.count || 0)
        .catch(() => 0);
}

// Функция для обновления информации о поездках при изменении даты
function updateTripsInfoOnDateChange() {
    const clientSelect = document.getElementById('client_id');
    const visitDataInput = document.getElementById('visit_data');
    const clientId = clientSelect?.value;
    const visitDate = visitDataInput?.value;
    
    if (clientId && visitDate) {
        updateClientTripsInfo(clientId, visitDate);
    } else {
        hideClientTripsInfo();
    }
}

// Функция для скрытия информации о поездках
function hideClientTripsInfo() {
    const clientTripsInfo = document.getElementById('client-trips-info');
    if (clientTripsInfo) {
        clientTripsInfo.style.display = 'none';
    }
}
</script>