<script>
    // Функция для обновления цены поездки и суммы возмещения taxi-type.blade.php
function updateTaxiPriceAndCompensation() {
    const typeOrder = {{ $type }}; // Тип заказа из PHP
    const taxiSelect = document.getElementById('taxi_id');
    const zenaTypeSelect = document.getElementById('zena_type');
    const taxiPriceInput = document.getElementById('taxi_price');
    const taxiVozmInput = document.getElementById('taxi_vozm');
    const skidkaDopAllInput = document.getElementById('skidka_dop_all');
    
    // Работаем только для легкового авто и ГАЗели (не для соцтакси)
    if (typeOrder == 1 || !taxiSelect || !zenaTypeSelect || !taxiPriceInput || !taxiVozmInput) {
        return;
    }
    
    const taxiId = taxiSelect.value;
    const zenaType = zenaTypeSelect.value || '1';
    
    // Если не выбран такси или тип поездки, очищаем поля
    if (!taxiId) {
        if (taxiPriceInput) taxiPriceInput.value = '';
        if (taxiVozmInput) taxiVozmInput.value = '';
        return;
    }
    
    // Получаем данные такси через AJAX
    fetch(`/api/taxis/${taxiId}`)
        .then(response => response.json())
        .then(data => {
            if (data.error) {
                console.error('Ошибка получения данных такси:', data.error);
                return;
            }
            
            let price = 0;
            let compensation = 0;
            
            // Определяем цену в зависимости от типа заказа и типа поездки
            if (typeOrder == 2) { // Легковое авто
                if (zenaType == '1') { // В одну сторону
                    price = parseFloat(data.zena1_auto) || 0;
                } else if (zenaType == '2') { // В обе стороны
                    price = parseFloat(data.zena2_auto) || 0;
                }
            } else if (typeOrder == 3) { // ГАЗель
                if (zenaType == '1') { // В одну сторону
                    price = parseFloat(data.zena1_gaz) || 0;
                } else if (zenaType == '2') { // В обе стороны
                    price = parseFloat(data.zena2_gaz) || 0;
                }
            }
     
            // Устанавливаем значения полей
            taxiPriceInput.value = price.toFixed(11);
            taxiVozmInput.value = price.toFixed(11);
        })
        .catch(error => {
            console.error('Ошибка получения данных такси:', error);
            if (taxiPriceInput) taxiPriceInput.value = '';
            if (taxiVozmInput) taxiVozmInput.value = '';
        });
}

</script>    