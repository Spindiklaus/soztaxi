<!--dopus-data.blade.php -->

<script>
// Функции работы с дополнительными условиями
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
            const typeOrder = {{ $order->zena_type }}; // Тип заказа из PHP
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

function clearDopusData() {
    const skidkaDopAllInput = document.getElementById('skidka_dop_all');
    const kolPLimitInput = document.getElementById('kol_p_limit');
    
    if (skidkaDopAllInput) skidkaDopAllInput.value = '';
    if (kolPLimitInput) kolPLimitInput.value = '';
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
</script>