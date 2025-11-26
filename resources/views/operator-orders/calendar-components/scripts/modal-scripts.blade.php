<!-- /resuces/views/operator-orders/calendar-components/scripts/modal-scripts.blade.php
<!-- JavaScript для переключения блоков информации и модального окна -->
    <script>
    function openCopyModal(orderId, visitDateTime, adresOtkuda, adresKuda) {
    // Заполняем форму данными из заказа
    document.getElementById('copy-order-id').value = orderId;
    // Форматируем дату для datetime-local (YYYY-MM-DDTHH:mm)
    const formattedDateTime = visitDateTime.replace(' ', 'T');
    document.getElementById('copy-visit-date-time').value = formattedDateTime;

    // --- НОВОЕ: Обновление текста в переключателях направления ---
    const labelDirTuda = document.getElementById('label-dir-tuda');
    const labelDirObratno = document.getElementById('label-dir-obratno');

    if (labelDirTuda && labelDirObratno) {
        labelDirTuda.textContent = `Туда: (${adresOtkuda} -> ${adresKuda})`;
        labelDirObratno.textContent = `Обратно: (${adresKuda} -> ${adresOtkuda})`;
    }
    // --- КОНЕЦ НОВОГО ---

    // Показываем модальное окно
    document.getElementById('copy-order-modal').classList.remove('hidden');
}

        function closeCopyModal() {
            // Сбрасываем форму
            document.getElementById('copy-order-form').reset();
            // Скрываем модальное окно
            document.getElementById('copy-order-modal').classList.add('hidden');
        }

        // Обработка отправки формы
        document.getElementById('copy-order-form').addEventListener('submit', function(e) {
            e.preventDefault(); // Останавливаем стандартную отправку формы

            const formData = new FormData(this);
            const orderId = formData.get('order_id');
            const visitData = formData.get('visit_data');
            const TypeKuda = formData.get('type_kuda');

            // Выполняем AJAX-запрос
            fetch(`{{ route('operator.social-taxi.copy-order') }}`, { // Нужно будет создать маршрут
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'), // Laravel CSRF токен
                    // Не указываем Content-Type, чтобы браузер сам установил multipart/form-data
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // alert('Заказ успешно создан!');
                    if (data.message) {
                        alert(data.message); // <--- Вот тут будет "Скидка изменена с 100% на 50% для категории 2"
                    }
                    closeCopyModal();
                    // Обновляем календарь
                    window.location.reload(); // 
                } else {
                    alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                console.error('Ошибка при копировании заказа:', error);
                alert('Произошла ошибка при отправке запроса.');
                closeCopyModal();
            });
        });

    </script>