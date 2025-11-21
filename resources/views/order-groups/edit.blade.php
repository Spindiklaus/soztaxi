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
                            <input type="datetime-local" name="visit_date" id="visit_date"
                                   value="{{ old('visit_date', $orderGroup->visit_date ? $orderGroup->visit_date->format('Y-m-d\TH:i') : '') }}" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">
                        </div>
                    </div>

                    <div class="mb-2">
                        <label for="komment" class="block text-gray-700 text-sm font-bold mb-2">Комментарий к группе:</label>
                        <textarea name="komment" id="komment" rows="2" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline">{{ old('komment', $orderGroup->komment) }}</textarea>
                        <p class="text-xs text-gray-500 mt-1">Максимум 1000 символов.</p>
                    </div>
                    
                    <div class="flex items-center justify-end mt-2 mb-6">
                        <button type="submit" class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Обновить группу
                        </button>
                    </div>
                </form>

                    @if($orderGroup->orders->isNotEmpty())
                        <div class="mb-2">
                            <div class="flex justify-between items-center mb-2">
                                <h3 class="text-lg font-medium text-gray-800">Заказы в группе:</h3>
                                <!-- Кнопка "Добавить заказ в группу" - отображается только если заказов меньше 3 -->
                                @if($orderGroup->orders->count() < 3)
                                    <button type="button" onclick="openAddOrderModal({{ $orderGroup->id }})" class="inline-flex items-center px-3 py-1 bg-green-600 border border-transparent rounded-md font-semibold text-white hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500 transition ease-in-out duration-150 text-sm">
                                        Добавить заказ в группу
                                    </button>
                                @else
                                    <span class="text-sm text-gray-500 italic">Группа заполнена (3/3)</span>
                                @endif
                            </div>
                            <div class="overflow-x-auto mb-2">
                                <table class="w-full table-auto divide-y divide-gray-200">
                                    <thead class="bg-gray-50">
                                        <tr>
                                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Время посадки</th>
                                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Откуда</th>
                                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Куда</th>
                                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Клиент</th>
                                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Предв. дальность</th>
                                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Заказ</th>
                                            <th scope="col" class="px-2 py-2 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Действия</th>
                                        </tr>
                                    </thead>
                                    <tbody class="bg-white divide-y divide-gray-200">
                                        @foreach($orderGroup->orders as $order)
                                            <tr>
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
                                                    {{ $order->client ? $order->client->fio : 'N/A' }}
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
                                                <td class="px-2 py-2 whitespace-nowrap text-sm font-medium">
                                                    <!-- Кнопка "Удалить из группы" - отображается, только если это НЕ самый ранний заказ  -->
                                                    @if($order->id !== $earliestOrderId)
                                                        <form method="POST" action="{{ route('order-groups.remove-order', ['orderGroup' => $orderGroup->id, 'order' => $order->id]) }}" class="inline-block" onsubmit="return confirm('Вы уверены, что хотите удалить этот заказ из группы?')">
                                                            @csrf
                                                            @method('DELETE')
                                                            <button type="submit" class="text-red-600 hover:text-red-900" title="Удалить заказ из группы">
                                                                <svg xmlns="http://www.w3.org/2000/svg" class="h-5 w-5 inline" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                                    <path stroke-linecap="round" stroke-linejoin="round" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                                                </svg>
                                                            </button>
                                                        </form>
                                                    @else
                                                        <!-- Если это самый ранний заказ, просто отображаем сообщение или оставляем пустую ячейку -->
                                                        <span class="text-xs text-gray-500">Основной</span>
                                                    @endif
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

                    
            </div>
        </div>
    </div>

     <!-- Подключаем модальное окно -->
    @include('order-groups.edit-components.add-order-modal')

     <!-- Подключаем скрипты -->
    @include('order-groups.scripts.modal_scripts')
    
</x-app-layout>