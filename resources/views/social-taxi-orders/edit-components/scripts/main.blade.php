<!--main.blade.php -->

<script>
document.addEventListener('DOMContentLoaded', function () {
    const clientSelect = document.getElementById('client_id');
    const categorySelect = document.getElementById('category_id');
    const dopusSelect = document.getElementById('dopus_id');
    const visitDataInput = document.getElementById('visit_data');
    const zenaTypeSelect = document.getElementById('zena_type');
    const adresObratnoInput = document.getElementById('adres_obratno');
    const typeOrder = {{ $order->zena_type }}; // Тип заказа из PHP
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
    
    // Инициализация состояния поля обратного адреса при загрузке страницы
    initializeAdresObratnoState();

    // Добавляем обработчик изменения типа поездки
    if (zenaTypeSelect) {
        zenaTypeSelect.addEventListener('change', function() {
            updateAdresObratnoState(this.value);
        });
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
    
    // Работаем только для легкового авто и ГАЗели
    if (typeOrder != 1 && taxiSelect && zenaTypeSelect) {
        // Делаем поля только для чтения
        const taxiPriceInput = document.getElementById('taxi_price');
        const taxiVozmInput = document.getElementById('taxi_vozm');
        if (taxiPriceInput) {
            taxiPriceInput.readOnly = true;
            taxiPriceInput.classList.add('bg-gray-100', 'cursor-not-allowed');
        }
        if (taxiVozmInput) {
            taxiVozmInput.readOnly = true;
            taxiVozmInput.classList.add('bg-gray-100', 'cursor-not-allowed');
        }
        
        // Добавляем обработчики событий
        taxiSelect.addEventListener('change', updateTaxiPriceAndCompensation);
        zenaTypeSelect.addEventListener('change', updateTaxiPriceAndCompensation);
        
        if (skidkaDopAllInput) {
            skidkaDopAllInput.addEventListener('change', updateTaxiPriceAndCompensation);
        }
        
        // Инициализация при загрузке страницы
        setTimeout(updateTaxiPriceAndCompensation, 250);
    }
    
 });
</script>