<script>
document.addEventListener('DOMContentLoaded', function () {
    const clientSelect = document.getElementById('client_id');

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
            }
            
            // Устанавливаем дополнительные поля из последнего заказа (только если они существуют)
            if (categorySkidkaInput && data.last_order_data.category_skidka !== undefined && data.last_order_data.category_skidka !== null) {
                categorySkidkaInput.value = data.last_order_data.category_skidka;
            }
            if (categoryLimitInput && data.last_order_data.category_limit !== undefined && data.last_order_data.category_limit !== null) {
                categoryLimitInput.value = data.last_order_data.category_limit;
            }
            if (dopusSelect && data.last_order_data.dopus_id) {
                dopusSelect.value = data.last_order_data.dopus_id;
            }
            if (skidkaDopAllInput && data.last_order_data.skidka_dop_all !== undefined && data.last_order_data.skidka_dop_all !== null) {
                skidkaDopAllInput.value = data.last_order_data.skidka_dop_all;
            }
            if (kolPLimitInput && data.last_order_data.kol_p_limit !== undefined && data.last_order_data.kol_p_limit !== null) {
                kolPLimitInput.value = data.last_order_data.kol_p_limit;
            }
        }
        
        // Если нет данных из последнего заказа, но есть категории клиента
        if (!data.last_order_data && data.client_categories && data.client_categories.length > 0) {
            // Устанавливаем первую доступную категорию клиента
            if (categorySelect) {
                categorySelect.value = data.client_categories[0] || '';
            }
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

        // Очищаем все поля данных клиента (только если элементы существуют)
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

    function showLoadingIndicator() {
        const clientSelect = document.getElementById('client_id');
        if (clientSelect) {
            clientSelect.disabled = true;
        }
    }

    function hideLoadingIndicator() {
        const clientSelect = document.getElementById('client_id');
        if (clientSelect) {
            clientSelect.disabled = false;
        }
    }
});
</script>