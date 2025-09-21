<script>
// Функции работы с данными клиента
function fetchClientData(clientId, typeOrder) {
    // Используем API маршрут
    fetch(`/api/social-taxi-orders/client-data/${clientId}?type_order=${typeOrder}`)
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
            populateClientData(data);
        })
        .catch(error => {
            console.error('Ошибка получения данных клиента:', error);
            alert('Ошибка получения данных клиента: ' + error.message);
        });
}

function populateClientData(data) {
    // Получаем элементы каждый раз заново
    const clientTelInput = document.getElementById('client_tel');
    const clientInvalidInput = document.getElementById('client_invalid');
    const clientSoprInput = document.getElementById('client_sopr');
    const categorySelect = document.getElementById('category_id');
    const categorySkidkaInput = document.getElementById('category_skidka');
    const categoryLimitInput = document.getElementById('category_limit');
    const dopusSelect = document.getElementById('dopus_id');
    const skidkaDopAllInput = document.getElementById('skidka_dop_all');
    const kolPLimitInput = document.getElementById('kol_p_limit');
    const typeOrder = {{ $type }}; // Тип заказа из PHP

    // Очищаем все поля перед заполнением
    clearClientData();

    // Заполняем поля данными из последнего заказа
    if (data.last_order_data) {
        if (clientTelInput) {
            clientTelInput.value = data.last_order_data.client_tel || '';
        }
        if (clientInvalidInput) {
            clientInvalidInput.value = data.last_order_data.client_invalid || '';
        }
        if (clientSoprInput) {
            clientSoprInput.value = data.last_order_data.client_sopr || '';
        }
        
     // Устанавливаем категорию из последнего заказа
    if (categorySelect && data.last_order_data.category_id) {
        categorySelect.value = data.last_order_data.category_id;
        // Загружаем данные категории
        fetchCategoryData(data.last_order_data.category_id)
            .then(() => {
                // После загрузки категории устанавливаем значения из последнего заказа
                setTimeout(() => {
                    // Устанавливаем значения из последнего заказа поверх данных категории
                    if (categorySkidkaInput && data.last_order_data.category_skidka !== null) {
                        categorySkidkaInput.value = data.last_order_data.category_skidka;
                    }
                    if (categoryLimitInput && data.last_order_data.category_limit !== null) {
                        categoryLimitInput.value = data.last_order_data.category_limit;
                    }
                    
                    // Обновляем поля скидки и лимита
                    updateDiscountAndLimitFields();
                    
                    // Устанавливаем дополнительные условия из последнего заказа
                    if (dopusSelect && data.last_order_data.dopus_id) {
                        dopusSelect.value = data.last_order_data.dopus_id;
                        // Загружаем данные дополнительных условий
                        fetchDopusData(data.last_order_data.dopus_id);
                    } else {
                        // Вызываем пересчет, если это соцтакси
                        if (typeOrder == 1) {
                            setTimeout(triggerCalculationIfNeeded, 100);
                        }
                    }
                }, 50);
            })
            .catch(error => {
                console.error('Ошибка при загрузке категории:', error);
                // Даже если ошибка, продолжаем работу
                if (typeOrder == 1) {
                    setTimeout(triggerCalculationIfNeeded, 100);
                }
            });
    }
    }
    // После заполнения данных клиента обновляем информацию о поездках
    setTimeout(() => {
        const clientId = document.getElementById('client_id')?.value;
        const visitDate = document.getElementById('visit_data')?.value;
        
        if (clientId && visitDate && data.last_order_data) {
            updateClientTripsInfo(clientId, visitDate);
        } else {
            hideClientTripsInfo();
        }
    }, 200);
}

function clearClientData() {
    // Получаем элементы каждый раз заново
    const clientTelInput = document.getElementById('client_tel');
    const clientInvalidInput = document.getElementById('client_invalid');
    const clientSoprInput = document.getElementById('client_sopr');
    const categorySelect = document.getElementById('category_id');
    const categorySkidkaInput = document.getElementById('category_skidka');
    const categoryLimitInput = document.getElementById('category_limit');
    const dopusSelect = document.getElementById('dopus_id');
    const skidkaDopAllInput = document.getElementById('skidka_dop_all');
    const kolPLimitInput = document.getElementById('kol_p_limit');

    // Очищаем все поля данных клиента
    if (clientTelInput) clientTelInput.value = '';
    if (clientInvalidInput) clientInvalidInput.value = '';
    if (clientSoprInput) clientSoprInput.value = '';
    if (categorySelect) categorySelect.value = '';
    if (categorySkidkaInput) categorySkidkaInput.value = '';
    if (categoryLimitInput) categoryLimitInput.value = '';
    if (dopusSelect) dopusSelect.value = '';
    if (skidkaDopAllInput) skidkaDopAllInput.value = '';
    if (kolPLimitInput) kolPLimitInput.value = '';
}
</script>