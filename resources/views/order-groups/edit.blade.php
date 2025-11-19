<!-- resources/views/order-groups/edit.blade.php -->

<x-app-layout>
    <div class="container mx-auto py-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-1 border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 leading-tight">Редактировать Группу: {{ $orderGroup->name }}</h2>
                <div class="mt-2">
                    <a href="{{ route('order-groups.index') . '?' . http_build_query($urlParams) }}"
                       class="mb-2 inline-flex items-center px-4 py-0 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Назад к списку
                    </a>
                </div>
            </div>
            <div class="p-2">
                <form action="{{ route('order-groups.update', $orderGroup) }}" method="POST">
                    @csrf
                    @method('PUT')

                    <!-- Скрытые поля для параметров фильтрации и сортировки -->
                    @if(isset($urlParams))
                        @foreach($urlParams as $key => $value)
                            <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                        @endforeach
                    @endif

                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                        <div class="mb-2 md:col-span-2">
                            <label for="name" class="block text-gray-700 text-sm font-bold mb-2">Название группы:</label>
                            <input type="text" name="name" id="name" value="{{ old('name', $orderGroup->name) }}" required class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                        <div class="mb-2 md:col-span-1">
                            <label for="visit_date" class="block text-gray-700 text-sm font-bold mb-2">Дата поездки (группы):</label>
                            <input type="datetime-local" name="visit_data" id="visit_date"
                                   value="{{ old('visit_data', $orderGroup->visit_date ? $orderGroup->visit_date->format('Y-m-d\TH:i') : '') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="komment" class="block text-gray-700 text-sm font-bold mb-2">Комментарий к группе:</label>
                        <textarea name="komment" id="komment" rows="2" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('komment', $orderGroup->komment) }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Максимум 1000 символов.</p>
                    </div>

                    @if($orderGroup->orders->isNotEmpty())
                        <div class="mb-2">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-medium text-gray-800">Заказы в группе:</h3>
                                <!-- Кнопка "Добавить заказ в группу" - отображается только если заказов меньше 3 -->
                                @if($orderGroup->orders->count() < 3)
                                    <button type="button" onclick="openAddOrderModal({{ $orderGroup->id }})" class="inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150 text-sm">
                                        Добавить заказ
                                    </button>
                                @else
                                    <span class="text-sm text-gray-500 italic">Группа заполнена (3/3)</span>
                                @endif
                            </div>
                            <div class="overflow-x-auto mb-2">
                                <table class="w-full table-auto divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Клиент</th>
                                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Время посадки</th>
                                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Откуда</th>
                                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Куда</th>
                                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Предв. дальность</th>
                                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Заказ</th>
                                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">id заказа</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($orderGroup->orders as $order)
                                            <tr>
                                                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $order->client ? $order->client->fio : 'N/A' }}
                                                </td>
                                                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $order->visit_data ? $order->visit_data->format('H:i') : 'N/A' }}
                                                </td>
                                                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $order->adres_otkuda }}
                                                    @if($order->adres_otkuda_info)
                                                        <div class="text-xs text-gray-500 mt-1 ml-4">
                                                            {{ $order->adres_otkuda_info }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $order->adres_kuda }}
                                                    @if($order->adres_kuda_info)
                                                        <div class="text-xs text-gray-500 mt-1 ml-4">
                                                            {{ $order->adres_kuda_info }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $order->predv_way }}
                                                </td>
                                                <td class="px-2 py-2 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    @php
                                                        $status = $order->currentStatus->statusOrder;
                                                        $colorClass = !empty($status->color) ? $status->color : 'bg-gray-100 text-gray-800';
                                                    @endphp
                                                    @if($order->deleted_at)
                                                        <div class="text-sm text-red-500" title="{{ $status->name }}">
                                                            <span class="font-bold">{{ $order->pz_nom }}</span> от {{ $order->pz_data->format('d.m.Y H:i') }}
                                                        </div>
                                                        <div class="text-xs text-red-600 mt-1">
                                                            Удален: {{ $order->deleted_at->format('d.m.Y H:i') }}
                                                        </div>
                                                    @else
                                                        <div class="text-sm text-gray-500 {{ $colorClass }}" title="{{ $status->name }}">
                                                            {{ $order->pz_nom }} от {{ $order->pz_data->format('d.m.Y H:i') }}
                                                        </div>
                                                    @endif
                                                </td>
                                                <td class="px-2 py-2 whitespace-nowrap text-sm text-gray-500">
                                                    {{ $order->id }}
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    @else
                        <div class="mb-6">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-medium text-gray-800">Заказы в группе:</h3>
                                <!-- Кнопка "Добавить заказ в группу" -->
                                <button type="button" onclick="openAddOrderModal({{ $orderGroup->id }})" class="inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150 text-sm">
                                    Добавить заказ
                                </button>
                            </div>
                            <p class="text-gray-700">В этой группе пока нет заказов.</p>
                        </div>
                    @endif

                    <div class="flex items-center justify-end mt-4">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Обновить
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Модальное окно для добавления заказа -->
    <div id="add-order-modal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full hidden z-50">
        <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white">
            <div class="mt-3">
                <h3 class="text-lg font-medium text-gray-900">Добавить заказ в группу</h3>
                <form id="add-order-form" method="POST">
                    @csrf
                    @method('POST')
                    <input type="hidden" id="modal-order-group-id" name="order_group_id">
                    <div class="mt-2 space-y-2">
                        <div>
                            <label for="modal-order-id" class="block text-sm font-medium text-gray-700">Выберите заказ</label>
                            <select id="modal-order-id" name="order_id" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500 sm:text-sm">
                                <option value="">Загрузка...</option>
                            </select>
                        </div>
                    </div>
                    <div class="items-center gap-2 mt-4">
                        <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600 focus:outline-none">Добавить</button>
                        <button type="button" onclick="closeAddOrderModal()" class="px-4 py-2 bg-gray-300 rounded-md hover:bg-gray-400 focus:outline-none">Отмена</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // --- НОВОЕ: JavaScript для модального окна добавления заказа ---
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
        // --- КОНЕЦ НОВОГО ---
    </script>
</x-app-layout>