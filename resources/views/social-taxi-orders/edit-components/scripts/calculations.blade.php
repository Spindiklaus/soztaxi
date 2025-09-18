<!--calculations.blade.php -->

<script>
// Функции расчетов и вспомогательные функции
function calculateValues() {
    const predvWayInput = document.getElementById('predv_way');
    const taxiSelect = document.getElementById('taxi_id');
    const skidkaDopAllInput = document.getElementById('skidka_dop_all');
    const calculationResults = document.getElementById('calculation-results');
    const fullTripPrice = document.getElementById('full-trip-price');
    const clientPaymentAmount = document.getElementById('client-payment-amount');
    const reimbursementAmount = document.getElementById('reimbursement-amount');
    const taxiName = document.getElementById('taxi-name');
    
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
    const typeOrder = {{ $order->zena_type }}; // Тип заказа из PHP
    const predvWayInput = document.getElementById('predv_way');
    const taxiSelect = document.getElementById('taxi_id');
    
    if (typeOrder == 1 && predvWayInput?.value && taxiSelect?.value) {
        setTimeout(calculateValues, 100);
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
</script>