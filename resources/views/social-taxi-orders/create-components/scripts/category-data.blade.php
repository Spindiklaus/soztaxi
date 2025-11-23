<script>
// Функции работы с данными категории
function fetchCategoryData(categoryId) {
    return fetch(`/api/helpers/categories/${categoryId}`)
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

function populateCategoryData(data) {
    const categorySkidkaInput = document.getElementById('category_skidka');
    const categoryLimitInput = document.getElementById('category_limit');
    const skidkaDopAllInput = document.getElementById('skidka_dop_all');
    const kolPLimitInput = document.getElementById('kol_p_limit');
    const typeOrder = {{ $type }}; // Тип заказа из PHP
    
    if (categorySkidkaInput) {
        categorySkidkaInput.value = data.skidka || '';
    }
    if (categoryLimitInput) {
        categoryLimitInput.value = data.kol_p || '';
    }
    
    // Автоматически обновляем поля скидки и лимита из категории
    if (skidkaDopAllInput && categorySkidkaInput) {
        skidkaDopAllInput.value = categorySkidkaInput.value || '';
    }
    if (kolPLimitInput && categoryLimitInput) {
        kolPLimitInput.value = categoryLimitInput.value || '';
    }
    
    // Проверяем специальное условие скидки
    setTimeout(() => {
        const clientSelect = document.getElementById('client_id');
        const visitDataInput = document.getElementById('visit_data');
        const clientId = clientSelect?.value;
        const visitDate = visitDataInput?.value;
        
        if (clientId && visitDate) {
            checkSpecialDiscountCondition(clientId, visitDate);
        }
    }, 300);
    
    // Вызываем пересчет, если это соцтакси
    if (typeOrder == 1) {
        setTimeout(triggerCalculationIfNeeded, 100);
    }
}

function clearCategoryData() {
    const categorySkidkaInput = document.getElementById('category_skidka');
    const categoryLimitInput = document.getElementById('category_limit');
    
    if (categorySkidkaInput) categorySkidkaInput.value = '';
    if (categoryLimitInput) categoryLimitInput.value = '';
}

// Функция для проверки специального условия скидки
function checkSpecialDiscountCondition(clientId, visitDate) {
    if (!clientId || !visitDate) return;
    
    const categorySelect = document.getElementById('category_id');
    const selectedOption = categorySelect?.options[categorySelect.selectedIndex];
    
    if (!selectedOption || !selectedOption.value) return;
    
    // Получаем kat_dop из data-атрибута
    const katDop = selectedOption.getAttribute('data-kat-dop');
    
    // Проверяем, что kat_dop = 2
    if (katDop == 2) {
        // Проверяем текущие значения
        const skidkaDopAllInput = document.getElementById('skidka_dop_all');
        const currentDiscount = parseInt(skidkaDopAllInput?.value) || 0;
        
        // Если скидка 100%, проверяем количество поездок
        if (currentDiscount == 100) {
            // Получаем количество поездок клиента в месяце
            const monthYear = visitDate.substring(0, 7); // YYYY-MM формат
            
            fetch(`/api/client-trips/${clientId}/${monthYear}`)
                .then(response => response.json())
                .then(tripsData => {
                    const  freeTripsCount = tripsData.freeCount || 0; // Используем число бесплатных поездок
                    
                    // Если Если бесплатных поездок >= 16, меняем скидку на 50%
                    if (freeTripsCount >= 16) {
                        if (skidkaDopAllInput) {
                            const previousValue = skidkaDopAllInput.value;
                            skidkaDopAllInput.value = '50';
                            
                            // Показываем alert оператору
                            alert('Внимание! У клиента с типом категории 2 уже совершено ' + 
                                  freeTripsCount + ' бесплатных поездок в этом месяце. ' +
                                  'Окончательная скидка автоматически изменена с 100% на 50% согласно правилам.');
                            
                            // Вызываем пересчет, если это соцтакси
                            const typeOrder = {{ $type }}; // Тип заказа из PHP
                            if (typeOrder == 1) {
                                setTimeout(triggerCalculationIfNeeded, 100);
                            }
                        }
                    }
                })
                .catch(error => {
                    console.error('Ошибка получения количества поездок:', error);
                });
        }
    }
}

// Добавляем функцию для ручного вызова проверки
function triggerSpecialDiscountCheck() {
    const clientSelect = document.getElementById('client_id');
    const visitDataInput = document.getElementById('visit_data');
    const clientId = clientSelect?.value;
    const visitDate = visitDataInput?.value;
    
    if (clientId && visitDate) {
        checkSpecialDiscountCondition(clientId, visitDate);
    }
}

// Добавляем обработчики событий для триггера проверки
document.addEventListener('DOMContentLoaded', function () {
    // Добавляем проверку при изменении даты поездки
    const visitDataInput = document.getElementById('visit_data');
    if (visitDataInput) {
        visitDataInput.addEventListener('change', function() {
            setTimeout(triggerSpecialDiscountCheck, 500);
        });
    }
    
    // Добавляем проверку при изменении клиента
    const clientSelect = document.getElementById('client_id');
    if (clientSelect) {
        clientSelect.addEventListener('change', function() {
            setTimeout(triggerSpecialDiscountCheck, 500);
        });
    }
    
    // Добавляем проверку при изменении скидки
    const skidkaDopAllInput = document.getElementById('skidka_dop_all');
    if (skidkaDopAllInput) {
        skidkaDopAllInput.addEventListener('change', function() {
            setTimeout(triggerSpecialDiscountCheck, 300);
        });
    }
});
</script>