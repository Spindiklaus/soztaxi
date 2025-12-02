    let currentMultipleOrderId = null;
    let originalVisitTime = null;
    let originalPredvWay = null;
    let originalAdresOtkuda = null;
    let originalAdresKuda = null;
    let originalDate = null; // Сохраняем дату копируемого заказа
    
    // Глобальные переменные для дат календаря ---
    const calendarEndDate = new Date(window.calendarEndDateFromPHP); // <-- ИСПОЛЬЗУЕМ window
    
    document.querySelectorAll('.copy-multiple-btn').forEach(button => {
    button.addEventListener('click', function() {
        const orderId = this.getAttribute('data-order-id');
        const visitDatetime = this.getAttribute('data-visit-datetime');
        const adresOtkuda = this.getAttribute('data-adres-otkuda');
        const adresKuda = this.getAttribute('data-adres-kuda');
        const predvWay = this.getAttribute('data-predv-way');
        const latestOtkuda = this.getAttribute('data-latest-adres-otkuda');
        const latestKuda = this.getAttribute('data-latest-adres-kuda');

        openCopyMultipleModal(orderId, visitDatetime, adresOtkuda, adresKuda, predvWay, latestOtkuda, latestKuda);
    });
});


    function openCopyMultipleModal(orderId, originalVisitDateTime, originalAdresOtkuda, originalAdresKuda, originalPredvWayParam, latestAdresOtkuda, latestAdresKuda) {
//        console.log('latestAdresOtkuda:', latestAdresOtkuda);
//        console.log('latestAdresKuda:', latestAdresKuda);
        currentMultipleOrderId = orderId;
        document.getElementById('copy-multiple-order-id').value = orderId;

        const [datePart, timePart] = originalVisitDateTime.split(' ');
        originalDate = datePart; // Сохраняем дату поездки
        originalVisitTime = timePart.substring(0, 5); // HH:MM
        originalPredvWay = originalPredvWayParam; // параметеры копируемого заказа
        originalAdresOtkuda = originalAdresOtkuda;
        originalAdresKuda = originalAdresKuda;
        
        const latestAdresOtkudaFromModal = latestAdresOtkuda; // Дата первоначального заказа из списка
        const latestAdresKudaFromModal = latestAdresKuda;     // 

        // Заполняем информацию об оригинальном заказе
        // Преобразуем YYYY-MM-DD → ДД.ММ.ГОД
        const [year, month, day] = datePart.split('-');
        const formattedDate = `${day}.${month}.${year}`;
        document.getElementById('original-date-info').textContent = formattedDate;
<!--        document.getElementById('original-order-info').textContent = `${orderId}`;-->
        <!--document.getElementById('original-date-info').textContent = datePart;-->
        document.getElementById('original-time-info').textContent = originalVisitTime;
        document.getElementById('original-way-info').textContent = originalPredvWay || 'N/A';
        document.getElementById('original-from-info').textContent = originalAdresOtkuda || '-';
        document.getElementById('original-to-info').textContent = originalAdresKuda || '';
        
        const originalOrderInfoBlock = document.getElementById('original-order-info-block');
        if (originalAdresOtkuda === latestAdresOtkudaFromModal && originalAdresKuda === latestAdresKudaFromModal) {
            originalOrderInfoBlock.classList.remove('bg-gray-50'); // Убираем старый цвет
            originalOrderInfoBlock.classList.remove('bg-light-steel-blue-100'); // Убираем старый цвет
            originalOrderInfoBlock.classList.add('bg-green-100');  // Добавляем зелёный
        } else {
            originalOrderInfoBlock.classList.remove('bg-gray-50'); // Убираем зелёный, если был
            originalOrderInfoBlock.classList.remove('bg-green-100'); // Убираем зелёный, если был                  
            originalOrderInfoBlock.classList.add('bg-light-steel-blue-100');      // Возвращаем серый
        }

        // Генерируем календарь
        generateCalendar(datePart, calendarEndDateFromPHP, latestAdresOtkudaFromModal, latestAdresKudaFromModal);

        // Показываем модальное окно
        document.getElementById('copy-order-multiple-modal').classList.remove('hidden');
    }

    //  Генерация календаря ---
    function generateCalendar(startDateStr, endDateStr, latestAdresOtkuda, latestAdresKuda) {
        console.log('latestAdresOtkuda:', latestAdresOtkuda);   
        console.log('originalAdresOtkuda:', originalAdresOtkuda);   
        const calendarContainer = document.getElementById('copy-calendar-container');
        const startDate = new Date(startDateStr);

        // --- ПРОВЕРКА ДАТЫ КОНЦА ---
        // const endDate = new Date(calendarEndDateFromPHP); // <-- УБРАНО
        if (!window.calendarEndDateFromPHP) { // <-- Проверяем window
            console.error('Не передана дата окончания календаря.');
            alert('Невозможно отобразить календарь: недостаточно данных.');
            return;
        }
        const endDate = new Date(window.calendarEndDateFromPHP); // <-- Используем window
        if (isNaN(endDate.getTime())) {
            console.error('Некорректная дата окончания календаря: ' + window.calendarEndDateFromPHP); // <-- Используем window
            alert('Невозможно отобразить календарь: некорректная дата окончания.');
            return;
        }
        // --- КОНЕЦ ПРОВЕРКИ ---

        const calendarMonth = new Date(startDate.getFullYear(), startDate.getMonth(), 1);
        const year = calendarMonth.getFullYear();

        let bgColorClass = 'bg-gray-50'; // Цвет по умолчанию
       
        
        // Генерируем HTML календаря
        let calendarHtml = `
            <div class="grid grid-cols-7 gap-1 mb-2">
                ${['Пн', 'Вт', 'Ср', 'Чт', 'Пт', 'Сб', 'Вс'].map(day => `<div class="text-center text-xs font-bold text-gray-500 py-1">${day}</div>`).join('')}
            </div>
            <div class="grid grid-cols-7 gap-1">
        `;

        // Определяем день недели первого дня месяца (1 = Пн, 7 = Вс)
        const startDayOfWeek = calendarMonth.getDay(); // Воскресенье = 0, Понедельник = 1, ..., Суббота = 6
        const adjustedStartDayOfWeek = startDayOfWeek === 0 ? 7 : startDayOfWeek; // Переводим в ISO (Пн = 1, ..., Вс = 7)

        // Вставляем пустые ячейки для дней перед первым днем месяца
        for (let i = 1; i < adjustedStartDayOfWeek; i++) {
            calendarHtml += '<div class="p-1 border border-gray-200 bg-gray-50 text-center text-sm text-gray-400"></div>';
        }

        // Инициализируем currentDate для цикла по дням месяца
        let currentDate = new Date(calendarMonth);
        const endDateOfMonth = new Date(calendarMonth.getFullYear(), calendarMonth.getMonth() + 1, 0); // Последний день месяца

        while (currentDate <= endDateOfMonth) {
            const year = currentDate.getFullYear();
            const month = String(currentDate.getMonth() + 1).padStart(2, '0');
            const day = String(currentDate.getDate()).padStart(2, '0');
            const dateStr = `${year}-${month}-${day}`;
            const dayNumber = currentDate.getDate();
            const isToday = currentDate.toDateString() === new Date().toDateString();
            const isSelectedOriginal = dateStr === originalDate; // Проверяем, является ли день оригинальной датой

            // Пропускаем оригинальную дату заказа
            if (isSelectedOriginal) {
                calendarHtml += `
                <div class="p-1 border border-gray-200 bg-gray-200 text-center text-sm text-gray-500 font-bold">${dayNumber}</div>`;
            } else if (currentDate <= endDate) { // <-- ИСПОЛЬЗУЕМ endDate из параметра
                // Только если дата в пределах календаря
                const isDisabled = false; // Пока все дни активны, можно добавить логику

                calendarHtml += `
                    <div class="p-1 border border-gray-200 ${isToday ? 'bg-blue-50' : 'bg-gray-50'}">
                        <!-- День и чекбокс в одной строке -->
                        <div class="flex items-center gap-1 mb-0.5">
                            <span class="text-xs font-medium text-gray-700">${dayNumber}</span>
                            <input type="checkbox" 
                                   name="selected_dates[${dateStr}][selected]" 
                                   value="1" 
                                   class="h-4 w-4 text-blue-600 border-gray-300 rounded focus:ring-blue-500" 
                                   ${isDisabled ? 'disabled' : ''}>
                        </div>
                        <!-- Время -->
                        <div class="mb-0.5">
                            <input type="time" 
                                   name="selected_dates[${dateStr}][visit_time]" 
                                   value="${originalVisitTime}" 
                                   required 
                                   class="w-full rounded-md border-gray-300 text-xs py-0.5 focus:ring-blue-500 focus:border-blue-500"
                                   ${isDisabled ? 'disabled' : ''}>
                        </div>
                        <!-- Предварительная дальность -->
                        <div>
                            <input type="number" 
                                   name="selected_dates[${dateStr}][predv_way]" 
                                   value="${originalPredvWay || ''}" 
                                   min="0" step="0.1" 
                                   class="w-full rounded-md border-gray-300 text-xs py-0.5 focus:ring-blue-500 focus:border-blue-500"
                                   ${isDisabled ? 'disabled' : ''}>
                        </div>
                    </div>
                `;
            } else {
                // Если дата выходит за пределы календаря, просто пустая ячейка
                calendarHtml += '<div class="p-1 border border-gray-200 bg-gray-100 text-center text-sm text-gray-400"></div>';
            }

            currentDate.setDate(currentDate.getDate() + 1);
        }

        // После цикла по дням месяца, определяем день недели последнего дня месяца
        const endDayOfWeek = endDateOfMonth.getDay(); // <-- Это будет 0 (Вс) или 1-6
        const adjustedEndDayOfWeek = endDayOfWeek === 0 ? 7 : endDayOfWeek; // <-- Преобразуем в ISO

        // Вставляем пустые ячейки для дней после последнего дня месяца до конца недели (воскресенья)
        for (let i = adjustedEndDayOfWeek + 1; i <= 7; i++) {
            calendarHtml += '<div class="p-1 border border-gray-200 bg-gray-100 text-center text-sm text-gray-400"></div>';
        }

        calendarHtml += '</div>'; // Закрываем grid-cols-7

        calendarContainer.innerHTML = calendarHtml;
    }

    function closeCopyMultipleModal() {
        document.getElementById('copy-order-multiple-modal').classList.add('hidden');
        // Сбрасываем форму
        document.getElementById('copy-order-multiple-form').reset();
        currentMultipleOrderId = null;
        originalVisitTime = null;
        originalPredvWay = null;
        originalAdresOtkuda = null;
        originalAdresKuda = null;
        originalDate = null; // Сбрасываем дату
    }
    
    

    // Обработка отправки формы множественного копирования
    document.getElementById('copy-order-multiple-form').addEventListener('submit', function(e) {
        e.preventDefault();

        // Отключаем поля для неотмеченных дат, чтобы они не попали в FormData
        const checkboxes = this.querySelectorAll('input[name*="[selected]"]');
        checkboxes.forEach(checkbox => {
            const dateKeyMatch = checkbox.name.match(/selected_dates\[([^\]]+)\]\[selected\]/);
            if (dateKeyMatch) {
                const dateKey = dateKeyMatch[1];
                const timeInput = this.querySelector(`input[name="selected_dates[${dateKey}][visit_time]"]`);
                const wayInput = this.querySelector(`input[name="selected_dates[${dateKey}][predv_way]"]`);
                if (!checkbox.checked) {
                    if (timeInput) timeInput.disabled = true;
                    if (wayInput) wayInput.disabled = true;
                } else {
                    // Убедимся, что отмеченные — включены
                    if (timeInput) timeInput.disabled = false;
                    if (wayInput) wayInput.disabled = false;
                }
            }
        });
        
        
        const formData = new FormData(this);
        const selectedCheckboxes = this.querySelectorAll('input[name*="[selected]"]:checked');
        if (selectedCheckboxes.length === 0) {
            alert('Пожалуйста, выберите хотя бы одну дату.');
            return;
        }

        // Выполняем AJAX-запрос
        fetch(window.copyMultipleApiUrl, {
            method: 'POST',
            body: formData,
            credentials: 'same-origin', // ← ЭТА СТРОКА ОБЯЗАТЕЛЬНА! для куки
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                'X-Requested-With': 'XMLHttpRequest',
            }
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message || 'Заказы успешно созданы!');
                closeCopyMultipleModal();
                location.reload();
            } else {
                alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
            }
        })
        .catch(error => {
            console.error('Ошибка при множественном копировании заказа:', error);
            alert('Произошла ошибка при отправке запроса.');
            closeCopyMultipleModal();
        });
    });
