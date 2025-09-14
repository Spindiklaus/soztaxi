<!-- resources/views/social-taxi-orders/create-components/scripts.blade.php -->
<script>
document.addEventListener('DOMContentLoaded', function () {
    const clientSelect = document.getElementById('client_id');
    const categorySelect = document.getElementById('category_id');
    const dopusSelect = document.getElementById('dopus_id');
    const visitDataInput = document.getElementById('visit_data');
    const zenaTypeSelect = document.getElementById('zena_type');
    const adresObratnoInput = document.getElementById('adres_obratno');
    const typeOrder = {{ $type }}; // Тип заказа из PHP
    const skidkaDopAllInput = document.getElementById('skidka_dop_all');
    
    // Добавленные переменные для расчета
    const predvWayInput = document.getElementById('predv_way');
    const taxiSelect = document.getElementById('taxi_id');
    const calculationResults = document.getElementById('calculation-results');
    const fullTripPrice = document.getElementById('full-trip-price');
    const clientPaymentAmount = document.getElementById('client-payment-amount');
    const reimbursementAmount = document.getElementById('reimbursement-amount');
    const taxiName = document.getElementById('taxi-name'); // Может быть null, это нормально
    
    // Сохраняем начальное состояние dopusSelect
    if (dopusSelect && typeOrder != 1) {
        dopusSelect.readOnly = true;
        dopusSelect.disabled = true;
    }

    if (clientSelect) {
        clientSelect.addEventListener('change', function () {
            const clientId = this.value;
            if (clientId) {
                fetchClientData(clientId, typeOrder);
            } else {
                // Очищаем поля при сбросе клиента
                clearClientData();
                hideClientTripsInfo();
            }
        });
    }

    // Добавляем обработчик изменения категории
    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            const categoryId = this.value;
            if (categoryId) {
                fetchCategoryData(categoryId);
                // Сбрасываем поля дополнительных условий при изменении категории
                resetDopusFields();
            } else {
                // Очищаем поля категории при сбросе
                clearCategoryData();
                // Сбрасываем поля дополнительных условий
                resetDopusFields();
            }
        });
    }

    // Добавляем обработчик изменения дополнительных условий
    if (dopusSelect) {
        dopusSelect.addEventListener('change', function () {
            const dopusId = this.value;
            if (dopusId) {
                fetchDopusData(dopusId);
            } else {
                // Очищаем поля дополнительных условий при сбросе
                restoreFromCategory();
            }
        });
    }

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

    // Получение данных категории по AJAX
    function fetchCategoryData(categoryId) {
        return fetch(`/api/categories/${categoryId}`)
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
                populateCategoryData(data);
                return data; // Возвращаем данные
            })
            .catch(error => {
                console.error('Ошибка получения данных категории:', error);
                alert('Ошибка получения данных категории: ' + error.message);
                throw error; // Пробрасываем ошибку дальше
            });
    }

    // Получение данных дополнительных условий по AJAX
    function fetchDopusData(dopusId) {
        return fetch(`/api/skidka-dops/${dopusId}`)
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
                populateDopusData(data);
                // Вызываем пересчет, если это соцтакси
                if (typeOrder == 1) {
                    setTimeout(triggerCalculationIfNeeded, 100);
                }
                return data;
            })
            .catch(error => {
                console.error('Ошибка получения данных дополнительных условий:', error);
                alert('Ошибка получения данных дополнительных условий: ' + error.message);
                throw error; // Пробрасываем ошибку дальше
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
    
    // Функция для обновления полей скидки и лимита
function updateDiscountAndLimitFields() {
    const skidkaDopAllInput = document.getElementById('skidka_dop_all');
    const kolPLimitInput = document.getElementById('kol_p_limit');
    const categorySkidkaInput = document.getElementById('category_skidka');
    const categoryLimitInput = document.getElementById('category_limit');
    
    // Обновляем поля скидки и лимита из категории
    if (skidkaDopAllInput && categorySkidkaInput) {
        skidkaDopAllInput.value = categorySkidkaInput.value || '';
    }
    if (kolPLimitInput && categoryLimitInput) {
        kolPLimitInput.value = categoryLimitInput.value || '';
    }
}

    // Заполнение данных категории
    function populateCategoryData(data) {
        const categorySkidkaInput = document.getElementById('category_skidka');
        const categoryLimitInput = document.getElementById('category_limit');
        const skidkaDopAllInput = document.getElementById('skidka_dop_all');
        const kolPLimitInput = document.getElementById('kol_p_limit');
        
        if (categorySkidkaInput) {
            categorySkidkaInput.value = data.skidka || '';
        }
        if (categoryLimitInput) {
            categoryLimitInput.value = data.kol_p || '';
        }
        
        // Автоматически обновляем поля скидки и лимита из категории
        if (skidkaDopAllInput && categorySkidkaInput) {
            skidkaDopAllInput.value = categorySkidkaInput.value || '';
            triggerCalculationIfNeeded(); 
        }
        if (kolPLimitInput && categoryLimitInput) {
            kolPLimitInput.value = categoryLimitInput.value || '';
        }
    }

    // Заполнение данных дополнительных условий
    function populateDopusData(data) {
        const skidkaDopAllInput = document.getElementById('skidka_dop_all');
        const kolPLimitInput = document.getElementById('kol_p_limit');
        
        if (skidkaDopAllInput) {
            skidkaDopAllInput.value = data.skidka || '';
            triggerCalculationIfNeeded(); // Добавляем вызов пересчета
        }
        if (kolPLimitInput) {
            kolPLimitInput.value = data.kol_p || '';
        }
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

    // Очистка данных категории
    function clearCategoryData() {
        const categorySkidkaInput = document.getElementById('category_skidka');
        const categoryLimitInput = document.getElementById('category_limit');
        
        if (categorySkidkaInput) categorySkidkaInput.value = '';
        if (categoryLimitInput) categoryLimitInput.value = '';
    }

    // Восстановление значений из категории при сбросе дополнительных условий
    function restoreFromCategory() {
        const skidkaDopAllInput = document.getElementById('skidka_dop_all');
        const kolPLimitInput = document.getElementById('kol_p_limit');
        const categorySkidkaInput = document.getElementById('category_skidka');
        const categoryLimitInput = document.getElementById('category_limit');
        triggerCalculationIfNeeded();
        
        // Восстанавливаем значения из категории
        if (skidkaDopAllInput && categorySkidkaInput) {
            skidkaDopAllInput.value = categorySkidkaInput.value || '';
        }
        if (kolPLimitInput && categoryLimitInput) {
            kolPLimitInput.value = categoryLimitInput.value || '';
        }
    }

    // Функция для сброса полей дополнительных условий
    function resetDopusFields() {
        const dopusSelect = document.getElementById('dopus_id');
        const skidkaDopAllInput = document.getElementById('skidka_dop_all');
        const kolPLimitInput = document.getElementById('kol_p_limit');
        const categorySkidkaInput = document.getElementById('category_skidka');
        const categoryLimitInput = document.getElementById('category_limit');
        
        // Сбрасываем селект дополнительных условий
        if (dopusSelect) {
            dopusSelect.value = '';
        }
        
        // Восстанавливаем значения из категории
        if (skidkaDopAllInput && categorySkidkaInput) {
            skidkaDopAllInput.value = categorySkidkaInput.value || '';
        }
        if (kolPLimitInput && categoryLimitInput) {
            kolPLimitInput.value = categoryLimitInput.value || '';
        }
    }

    // Очистка данных дополнительных условий
    function clearDopusData() {
        const skidkaDopAllInput = document.getElementById('skidka_dop_all');
        const kolPLimitInput = document.getElementById('kol_p_limit');
        
        if (skidkaDopAllInput) skidkaDopAllInput.value = '';
        if (kolPLimitInput) kolPLimitInput.value = '';
    }
    
    if (visitDataInput) {
        // Принудительное округление до 5 минут
        visitDataInput.addEventListener('input', function() {
            roundToFiveMinutes(this);
            // Обновляем информацию о поездках при изменении даты
            updateTripsInfoOnDateChange();
        });
        
        visitDataInput.addEventListener('change', function() {
            roundToFiveMinutes(this);
            // Обновляем информацию о поездках при изменении даты
            updateTripsInfoOnDateChange();
        });
    }
    
    // Функция для обновления информации о поездках при изменении даты
function updateTripsInfoOnDateChange() {
    const clientId = clientSelect?.value;
    const visitDate = visitDataInput?.value;
    
    if (clientId && visitDate) {
        updateClientTripsInfo(clientId, visitDate);
    } else {
        hideClientTripsInfo();
    }
}
    
    function roundToFiveMinutes(element) {
        if (!element.value) return;
        
        // Разбираем значение
        const [datePart, timePart] = element.value.split('T');
        const [hours, minutes] = timePart.split(':').map(Number);
        
        // Округляем минуты до ближайших 5
        const roundedMinutes = Math.round(minutes / 5) * 5;
        
        // Корректируем часы, если минуты стали 60
        let finalHours = hours;
        let finalMinutes = roundedMinutes;
        
        if (roundedMinutes === 60) {
            finalHours = (hours + 1) % 24;
            finalMinutes = 0;
        }
        
        // Форматируем минуты с ведущим нулем
        const formattedMinutes = finalMinutes.toString().padStart(2, '0');
        const formattedHours = finalHours.toString().padStart(2, '0');
        
        // Устанавливаем округленное значение
        element.value = `${datePart}T${formattedHours}:${formattedMinutes}`;
    }
    
    
    
    // Инициализация состояния поля обратного адреса при загрузке страницы
    initializeAdresObratnoState();

    // Добавляем обработчик изменения типа поездки
    if (zenaTypeSelect) {
        zenaTypeSelect.addEventListener('change', function() {
            updateAdresObratnoState(this.value);
        });
    }

    
    
    // Инициализация состояния поля обратного адреса
    function initializeAdresObratnoState() {
        // Только для легкового авто и ГАЗели
        if (typeOrder != 2 && typeOrder != 3) {
            return;
        }
        
        if (zenaTypeSelect && adresObratnoInput) {
            const zenaType = zenaTypeSelect.value || '1'; // По умолчанию "в одну сторону"
            updateAdresObratnoState(zenaType);
        }
    }

    // Обновление состояния поля обратного адреса в зависимости от типа поездки
    function updateAdresObratnoState(zenaType) {
        const adresObratnoInput = document.getElementById('adres_obratno');
        
        if (!adresObratnoInput) {
            return;
        }
        
        if (zenaType == '1') {
            // В одну сторону - очищаем и делаем только для чтения
            adresObratnoInput.value = '';
            adresObratnoInput.readOnly = true;
            adresObratnoInput.disabled = true;
            adresObratnoInput.classList.add('bg-gray-100', 'cursor-not-allowed');
            adresObratnoInput.placeholder = 'Поле недоступно для поездки в одну сторону';
        } else if (zenaType == '2') {
            // В обе стороны - делаем доступным для записи
            adresObratnoInput.readOnly = false;
            adresObratnoInput.disabled = false;
            adresObratnoInput.classList.remove('bg-gray-100', 'cursor-not-allowed');
            adresObratnoInput.placeholder = 'Введите обратный адрес';
        }
    }
    
    // Показываем расчеты только для соцтакси
    if (typeOrder == 1 && predvWayInput && taxiSelect) {
        // Добавляем обработчики событий
        const calculateTriggerElements = [predvWayInput, taxiSelect];
        if (dopusSelect) calculateTriggerElements.push(dopusSelect);
        if (skidkaDopAllInput) calculateTriggerElements.push(skidkaDopAllInput);
        
        calculateTriggerElements.forEach(element => {
            if (element) {
                element.addEventListener('input', debounce(calculateValues, 500));
                element.addEventListener('change', calculateValues);
            }
        });
        
        // Инициализация при загрузке страницы, если есть необходимые значения
        setTimeout(calculateValues, 100);
    }
    
    function calculateValues() {
    const predvWay = predvWayInput?.value;
    const taxiId = taxiSelect?.value;
    
    // Получаем скидку из скрытого поля или из других источников
    let discount = 0;
    if (skidkaDopAllInput) {
        discount = parseInt(skidkaDopAllInput.value) || 0;
    }
    
    // Проверяем, что есть все необходимые данные
    if (!predvWay || predvWay <= 0 || !taxiId) {
        if (calculationResults) {
            calculationResults.style.display = 'none';
        }
        return;
    }
    
    // Делаем AJAX-запрос для расчета значений
    fetch('/api/calculate-social-taxi-values', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify({
            predv_way: parseFloat(predvWay),
            taxi_id: parseInt(taxiId),
            skidka_dop_all: discount
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success && calculationResults && fullTripPrice && clientPaymentAmount && reimbursementAmount) {
            // Показываем блок с расчетами
            calculationResults.style.display = 'block';
            
            // Обновляем значения
            fullTripPrice.textContent = formatNumber(data.full_trip_price);
            clientPaymentAmount.textContent = formatNumber(data.client_payment_amount);
            reimbursementAmount.textContent = formatNumber(data.reimbursement_amount);
            if (taxiName) {
                taxiName.textContent = data.taxi_name;
            }
        } else {
            if (calculationResults) {
                calculationResults.style.display = 'none';
            }
            if (data.error) {
                console.error('Ошибка расчета:', data.error);
            }
        }
    })
    .catch(error => {
        if (calculationResults) {
            calculationResults.style.display = 'none';
        }
        console.error('Ошибка запроса:', error);
    });
}
    
    function formatNumber(num) {
        return parseFloat(num).toFixed(2).replace('.', ',').replace(/\B(?=(\d{3})+(?!\d))/g, ' ');
    }
    
    // Функция для debounce (отложенного выполнения)
    // для предотвращения множественных запросов при быстром вводе
    function debounce(func, wait) {
        let timeout;
        return function executedFunction(...args) {
            const later = () => {
                clearTimeout(timeout);
                func(...args);
            };
            clearTimeout(timeout);
            timeout = setTimeout(later, wait);
        };
    }
    
    // Функция для вызова предварительного расчета после обновления полей
    function triggerCalculationIfNeeded() {
        if (typeOrder == 1 && predvWayInput?.value && taxiSelect?.value) {
            setTimeout(calculateValues, 100);
        }
    }
    
    // Функция для обновления информации о поездках клиента
function updateClientTripsInfo(clientId, visitDate) {
    if (!clientId || !visitDate) {
        hideClientTripsInfo();
        return;
    }
    
    const monthYear = visitDate.substring(0, 7); // YYYY-MM формат
    const clientTripsInfo = document.getElementById('client-trips-info');
    const clientTripsButton = document.getElementById('client-trips-button');
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
        fetchClientActualTripsCount(clientId, monthYear),
        fetchClientTaxiSentTripsCount(clientId, monthYear)
    ])
    .then(([tripsCount, actualTripsCount, taxiSentTripsCount]) => {
        if (clientTripsButton) {
            clientTripsButton.innerHTML = tripsCount;
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

// Функция для скрытия информации о поездках
function hideClientTripsInfo() {
    const clientTripsInfo = document.getElementById('client-trips-info');
    if (clientTripsInfo) {
        clientTripsInfo.style.display = 'none';
    }
}
    
 });
</script>