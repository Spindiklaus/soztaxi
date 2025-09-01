<x-app-layout>
    <!-- ТЕСТ: Файл social-taxi-orders/index.blade.php -->
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Заголовок и кнопки -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-3xl font-bold text-gray-800">Заказы</h1>

                <div class="space-x-2 flex">
                    <a href="{{ route('import.orders.form') }}"
                       class="inline-flex items-center px-4 py-2 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700"
                       title="Импортировать заказы из CSV">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4" />
                        </svg>
                        Импортировать CSV
                    </a>

                    <a href="{{ route('social-taxi-orders.create') }}"
                       class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150"
                       title="Создать запись">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 mr-1 text-blue-200" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                        </svg>
                        Добавить заказ
                    </a>
                </div>
            </div>

            <!-- Фильтры -->
            <form action="{{ route('social-taxi-orders.index') }}" method="GET" class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                <div>
                    <label for="filter_pz_nom" class="block text-sm font-medium text-gray-700">Номер заказа</label>
                    <input type="text" name="pz_nom" id="filter_pz_nom" value="{{ request('pz_nom') }}" placeholder="%Поиск%" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                </div>

                <div>
                    <label for="filter_type_order" class="block text-sm font-medium text-gray-700">Тип заказа</label>
                    <select name="type_order" id="filter_type_order" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="">Все</option>
                        <option value="1" {{ request('type_order') == '1' ? 'selected' : '' }}>Соцтакси</option>
                        <option value="2" {{ request('type_order') == '2' ? 'selected' : '' }}>Легковое авто</option>
                        <option value="3" {{ request('type_order') == '3' ? 'selected' : '' }}>ГАЗель</option>
                    </select>
                </div>

                <div>
                    <label for="show_deleted" class="block text-sm font-medium text-gray-700">Статус записей</label>
                    <select name="show_deleted" id="show_deleted" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                        <option value="0" {{ request('show_deleted', '0') == '0' ? 'selected' : '' }}>Только активные</option>
                        <option value="1" {{ request('show_deleted') == '1' ? 'selected' : '' }}>Все (включая удаленные)</option>
                    </select>
                </div>

                <div class="flex justify-end space-x-2 md:col-span-1">
                    <button type="submit"
                            class="inline-flex items-center px-4 py-2 bg-blue-600 border border-transparent rounded-md font-semibold text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition ease-in-out duration-150">
                        Применить фильтр
                    </button>
                    <a href="{{ route('social-taxi-orders.index') }}"
                       class="inline-flex items-center px-4 py-2 bg-gray-300 border border-transparent rounded-md font-semibold text-gray-800 hover:bg-gray-400 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150">
                        Очистить фильтр
                    </a>
                </div>
            </form>

            <!-- Таблица -->
            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-800 text-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Заказ и статус</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Дата и время поездки</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Маршрут</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Клиент</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">Скидка и лимит</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">Действия</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($orders as $order)
                            <tr @if($order->deleted_at) class="bg-red-50" @endif>
                                <td class="px-6 py-4">
                                    @if($order->deleted_at)
                                        <div class="text-sm font-medium text-red-600">
                                            {{ getOrderTypeName($order->type_order) }}
                                        </div>
                                        <div class="text-sm text-red-500">
                                            № <span class="font-bold">{{ $order->pz_nom }}</span> от {{ $order->pz_data->format('d.m.Y H:i') }}
                                        </div>
                                    @else
                                        <div class="text-sm {{ getOrderTypeColor($order->type_order) }}">
                                            {{ getOrderTypeName($order->type_order) }}
                                        </div>
                                        <div class="text-sm text-gray-500">
                                            № {{ $order->pz_nom }} от {{ $order->pz_data->format('d.m.Y H:i') }}
                                        </div>
                                    @endif
                                    @if($order->deleted_at)
                                    <div class="text-xs text-red-600 mt-1">
                                        Удален: {{ $order->deleted_at->format('d.m.Y H:i') }}
                                    </div>
                                    @endif
                                    <div class="mt-2">
                                        @if($order->currentStatus && $order->currentStatus->statusOrder)
                                            @php
                                                $status = $order->currentStatus->statusOrder;
                                                // Используем цвет из базы данных или дефолтный серый
                                                $colorClass = !empty($status->color) ? $status->color : 'bg-gray-100 text-gray-800';
                                            @endphp
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium {{ $colorClass }}">
                                                {{ $status->name }}
                                            </span>
                                        @else
                                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                                Нет статуса
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($order->visit_data)
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $order->visit_data->format('d.m.Y') }}
                                    </div>
                                    <div class="text-sm text-gray-500">
                                        {{ $order->visit_data->format('H:i') }}
                                    </div>
                                    @else
                                    <div class="text-sm text-gray-500">-</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="text-sm text-gray-900">
                                        <span class="font-medium">Откуда:</span> {{ $order->adres_otkuda }}
                                    </div>
                                    <div class="text-sm text-gray-900 mt-1">
                                        <span class="font-medium">Куда:</span> {{ $order->adres_kuda }}
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @if($order->client)
                                    <div class="text-sm font-medium text-gray-900">
                                        {{ $order->client->fio }}
                                    </div>
                                    @if($order->client->rip_at)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-800 text-white">
                                        RIP: {{ $order->client->rip_at->format('d.m.Y') }}
                                    </span>
                                    @endif
                                    @else
                                    <div class="text-sm text-gray-500">Клиент не найден</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    @if($order->skidka_dop_all !== null)
                                    <div class="text-sm text-gray-900">
                                        Скидка: <span class="font-medium">{{ $order->skidka_dop_all }}%</span>
                                    </div>
                                    @else
                                    <div class="text-sm text-gray-500">Скидка: -</div>
                                    @endif
                                    @if($order->kol_p_limit !== null)
                                    <div class="text-sm text-gray-900 mt-1">
                                        Лимит: <span class="font-medium">{{ $order->kol_p_limit }} поездок/мес</span>
                                    </div>
                                    @else
                                    <div class="text-sm text-gray-500 mt-1">Лимит: -</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex flex-col space-y-1">
                                        <a href="{{ route('social-taxi-orders.show', $order) }}" class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-md hover:bg-blue-200 text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                            Просмотр
                                        </a>
                                        <a href="{{ route('social-taxi-orders.edit', $order) }}" class="inline-flex items-center px-3 py-1 bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200 text-sm">
                                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                            </svg>
                                            Редактировать
                                        </a>
                                        @if(!$order->deleted_at)
                                        <form action="{{ route('social-taxi-orders.destroy', $order) }}" method="POST" class="inline" onsubmit="return confirm('Вы уверены, что надо удалить заказ?')">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-red-100 text-red-800 rounded-md hover:bg-red-200 text-sm w-full">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                </svg>
                                                Удалить
                                            </button>
                                        </form>
                                        @else
                                        <form action="{{ route('social-taxi-orders.restore', $order) }}" method="POST" class="inline">
                                            @csrf
                                            @method('PATCH')
                                            <button type="submit" class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-md hover:bg-green-200 text-sm w-full">
                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                                </svg>
                                                Восстановить
                                            </button>
                                        </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    Заказы не найдены
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            <!-- Пагинация -->
            <div class="mt-4">
                {{ $orders->appends(request()->except('page'))->links() }}
            </div>
        </div>
    </div>
</x-app-layout>