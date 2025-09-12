<script>
document.addEventListener('DOMContentLoaded', function () {
    const clientSelect = document.getElementById('client_id');
    const categorySelect = document.getElementById('category_id');
    const dopusSelect = document.getElementById('dopus_id');
    const visitDataInput = document.getElementById('visit_data');

    if (clientSelect) {
        clientSelect.addEventListener('change', function () {
            const clientId = this.value;
            if (clientId) {
                fetchClientData(clientId);
            } else {
                // Очищаем поля при сбросе клиента
                clearClientData();
            }
        });
    }

    // Добавляем обработчик изменения категории
    if (categorySelect) {
        categorySelect.addEventListener('change', function () {
            const categoryId = this.value;
            if (categoryId) {
                fetchCategoryData(categoryId);
            } else {
                // Очищаем поля категории при сбросе
                clearCategoryData();
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
                clearDopusData();
            }
        });
    }

    function fetchClientData(clientId) {
        // Показываем индикатор загрузки
        showLoadingIndicator();

        // Используем API маршрут
        fetch(`/api/social-taxi-orders/client-data/${clientId}`)
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
                hideLoadingIndicator();
                alert('Ошибка получения данных клиента: ' + error.message);
            });
    }

    // Получение данных категории по AJAX
    function fetchCategoryData(categoryId) {
        showLoadingIndicator();
        
        fetch(`/api/categories/${categoryId}`)
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
            })
            .catch(error => {
                console.error('Ошибка получения данных категории:', error);
                hideLoadingIndicator();
                alert('Ошибка получения данных категории: ' + error.message);
            });
    }

    // Получение данных дополнительных условий по AJAX
    function fetchDopusData(dopusId) {
        showLoadingIndicator();
        
        fetch(`/api/skidka-dops/${dopusId}`)
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
            })
            .catch(error => {
                console.error('Ошибка получения данных дополнительных условий:', error);
                hideLoadingIndicator();
                alert('Ошибка получения данных дополнительных условий: ' + error.message);
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
                // После установки категории заполняем связанные поля
                fetchCategoryData(data.last_order_data.category_id);
            }
            
            // Устанавливаем дополнительные условия из последнего заказа
            if (dopusSelect && data.last_order_data.dopus_id) {
                dopusSelect.value = data.last_order_data.dopus_id;
                // После установки доп.условий заполняем связанные поля
                fetchDopusData(data.last_order_data.dopus_id);
            }
            
            // Устанавливаем значения полей из последнего заказа
            if (categorySkidkaInput && data.last_order_data.category_skidka !== null) {
                categorySkidkaInput.value = data.last_order_data.category_skidka;
            }
            if (categoryLimitInput && data.last_order_data.category_limit !== null) {
                categoryLimitInput.value = data.last_order_data.category_limit;
            }
            if (skidkaDopAllInput && data.last_order_data.skidka_dop_all !== null) {
                skidkaDopAllInput.value = data.last_order_data.skidka_dop_all;
            }
            if (kolPLimitInput && data.last_order_data.kol_p_limit !== null) {
                kolPLimitInput.value = data.last_order_data.kol_p_limit;
            }
        }
        
        // Если нет данных из последнего заказа, но есть категории клиента
        if (!data.last_order_data && data.client_categories.length > 0) {
            // Устанавливаем первую доступную категорию клиента
            if (categorySelect) {
                categorySelect.value = data.client_categories[0] || '';
                // После установки категории заполняем связанные поля
                if (data.client_categories[0]) {
                    fetchCategoryData(data.client_categories[0]);
                }
            }
        }

        hideLoadingIndicator();
    }

    // Заполнение данных категории
    function populateCategoryData(data) {
        const categorySkidkaInput = document.getElementById('category_skidka');
        const categoryLimitInput = document.getElementById('category_limit');
        
        if (categorySkidkaInput) {
            categorySkidkaInput.value = data.skidka || '';
        }
        if (categoryLimitInput) {
            categoryLimitInput.value = data.kol_p || '';
        }
        
        hideLoadingIndicator();
    }

    // Заполнение данных дополнительных условий
    function populateDopusData(data) {
        const skidkaDopAllInput = document.getElementById('skidka_dop_all');
        const kolPLimitInput = document.getElementById('kol_p_limit');
        
        if (skidkaDopAllInput) {
            skidkaDopAllInput.value = data.skidka || '';
        }
        if (kolPLimitInput) {
            kolPLimitInput.value = data.kol_p || '';
        }
        
        hideLoadingIndicator();
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

    // Очистка данных дополнительных условий
    function clearDopusData() {
        const skidkaDopAllInput = document.getElementById('skidka_dop_all');
        const kolPLimitInput = document.getElementById('kol_p_limit');
        
        if (skidkaDopAllInput) skidkaDopAllInput.value = '';
        if (kolPLimitInput) kolPLimitInput.value = '';
    }

    function showLoadingIndicator() {
        const clientSelect = document.getElementById('client_id');
        const categorySelect = document.getElementById('category_id');
        const dopusSelect = document.getElementById('dopus_id');
        
        if (clientSelect) clientSelect.disabled = true;
        if (categorySelect) categorySelect.disabled = true;
        if (dopusSelect) dopusSelect.disabled = true;
    }

    function hideLoadingIndicator() {
        const clientSelect = document.getElementById('client_id');
        const categorySelect = document.getElementById('category_id');
        const dopusSelect = document.getElementById('dopus_id');
        
        if (clientSelect) clientSelect.disabled = false;
        if (categorySelect) categorySelect.disabled = false;
        if (dopusSelect) dopusSelect.disabled = false;
    }
    
    if (visitDataInput) {
        // Принудительное округление до 5 минут
        visitDataInput.addEventListener('input', function() {
            roundToFiveMinutes(this);
        });
        
        visitDataInput.addEventListener('change', function() {
            roundToFiveMinutes(this);
        });
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
    
});
</script>