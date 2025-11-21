<script>
        // JavaScript для модального окна добавления заказа ---
        let currentGroupId = null; // Переменная для хранения ID группы

        function openAddOrderModal(groupId) {
            currentGroupId = groupId;
            document.getElementById('modal-order-group-id').value = groupId;

            const select = document.getElementById('modal-order-id');
            select.innerHTML = '<option value="">Загрузка...</option>'; // Сбрасываем список

            // Загружаем заказы для группы
            fetch(`/api/order-groups/${groupId}/available-orders`)
                .then(response => response.json())
                .then(data => {
                    select.innerHTML = ''; // Очищаем список
                    if (data.success && data.orders.length > 0) {
                        data.orders.forEach(order => {
                            const option = document.createElement('option');
                            option.value = order.id;
                            // Форматируем время из visit_data (предполагается формат 'Y-m-d H:i:s')
                            const visitDateTime = order.visit_data ? new Date(order.visit_data.replace(' ', 'T')) : null;
                            const formattedTime = visitDateTime ? visitDateTime.toLocaleTimeString('ru-RU', { hour: '2-digit', minute: '2-digit' }) : 'N/A';
                            // Форматируем дату (если нужно отображать дату тоже)
                            // const formattedDate = visitDateTime ? visitDateTime.toLocaleDateString('ru-RU') : 'N/A';
                            
                            // --- Форматирование ФИО ---
                            let clientInitials = 'N/A';
                            if (order.client && order.client.fio) {
                                const parts = order.client.fio.trim().split(/\s+/); // Разбиваем по пробелам
                                if (parts.length >= 2) {
                                    const lastName = parts[0];
                                    const firstName = parts[1];
                                    const patronymic = parts.length > 2 ? parts[2] : ''; // Может быть пустым

                                    // Берём первую букву имени и отчества, если есть
                                    const firstInitial = firstName.charAt(0).toUpperCase() + '.';
                                    const patronymicInitial = patronymic ? patronymic.charAt(0).toUpperCase() + '.' : '';

                                    clientInitials = `${lastName} ${firstInitial}${patronymic ? patronymicInitial : ''}`;
                                } else {
                                    // Если ФИО не содержит как минимум фамилию и имя, выводим как есть
                                    clientInitials = order.client.fio;
                                }
                            }
                           
                            option.textContent = `${formattedTime} - ${order.adres_otkuda} - ${order.adres_kuda} - ${clientInitials}`;
                            select.appendChild(option);
                        });
                    } else {
                        select.innerHTML = '<option value="">Нет доступных заказов</option>';
                    }
                })
                .catch(error => {
                    console.error('Ошибка загрузки заказов:', error);
                    select.innerHTML = '<option value="">Ошибка загрузки</option>';
                });

            document.getElementById('add-order-modal').classList.remove('hidden');
        }

        function closeAddOrderModal() {
            document.getElementById('add-order-modal').classList.add('hidden');
            // Сбрасываем форму
            document.getElementById('add-order-form').reset();
            currentGroupId = null; // Сбрасываем ID группы
        }

        // Обработка отправки формы добавления
        document.getElementById('add-order-form').addEventListener('submit', function(e) {
            e.preventDefault();

            const formData = new FormData(this);
            const groupId = formData.get('order_group_id');
            const orderId = formData.get('order_id');

            if (!orderId) {
                alert('Пожалуйста, выберите заказ.');
                return;
            }

            // Выполняем AJAX-запрос
            fetch(`/api/order-groups/${groupId}/add-order`, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                    'X-Requested-With': 'XMLHttpRequest', // Laravel использует это для определения AJAX-запросов
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert(data.message);
                    closeAddOrderModal();
                    // Обновляем страницу, чтобы отобразить новый заказ
                    location.reload();
                } else {
                    alert('Ошибка: ' + (data.message || 'Неизвестная ошибка'));
                }
            })
            .catch(error => {
                console.error('Ошибка при добавлении заказа:', error);
                alert('Произошла ошибка при отправке запроса.');
                closeAddOrderModal();
            });
        });
    </script>
