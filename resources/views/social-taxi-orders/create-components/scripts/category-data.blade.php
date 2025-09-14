<script>
// Функции работы с данными категории
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
        triggerCalculationIfNeeded(); 
    }
    if (kolPLimitInput && categoryLimitInput) {
        kolPLimitInput.value = categoryLimitInput.value || '';
    }
}

function clearCategoryData() {
    const categorySkidkaInput = document.getElementById('category_skidka');
    const categoryLimitInput = document.getElementById('category_limit');
    
    if (categorySkidkaInput) categorySkidkaInput.value = '';
    if (categoryLimitInput) categoryLimitInput.value = '';
}
</script>