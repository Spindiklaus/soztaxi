<!-- social-taxi-orders.index-components/table.blade.php -->

<div x-data="{
     sortField: '{{ $sort ?? 'pz_data' }}',
     sortDirection: '{{ $direction ?? 'desc' }}',
     sortBy(field) {
     if (this.sortField === field) {
     this.sortDirection = this.sortDirection === 'asc' ? 'desc' : 'asc';
     } else {
     this.sortField = field;
     this.sortDirection = 'desc';
     }
     let url = new URL(window.location);
     url.searchParams.set('sort', field);
     url.searchParams.set('direction', this.sortDirection);
     window.location.href = url.toString();
     }
     }" x-cloak class="bg-white shadow overflow-hidden sm:rounded-lg">
    <!-- Для отладки -->
    <!--    <div class="p-4 text-sm text-gray-600">
            Текущая сортировка: <strong x-text="`Поле: ${sortField}, Направление: ${sortDirection}`"></strong>
        </div>  -->
    <div class="overflow-auto max-h-[70vh]">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-800 text-gray-200 sticky top-0 z-10 shadow-lg">
                <tr>
                    <th @click="sortBy('pz_data')" scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                        Заказ и статус
                        <span class="ml-1" x-show="sortField === 'pz_data' && sortDirection === 'asc'">↑</span>
                        <span class="ml-1" x-show="sortField === 'pz_data' && sortDirection === 'desc'">↓</span>
                    </th>
                    <th @click="sortBy('visit_data')" scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                        Дата и время поездки
                        <span class="ml-1" x-show="sortField === 'visit_data' && sortDirection === 'asc'">↑</span>
                        <span class="ml-1" x-show="sortField === 'visit_data' && sortDirection === 'desc'">↓</span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                        Маршрут поездки
                    </th>
                    <th @click="sortBy('client_fio')" scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider cursor-pointer hover:bg-blue-700">
                        Клиент
                        <span class="ml-1" x-show="sortField === 'client_fio' && sortDirection === 'asc'">↑</span>
                        <span class="ml-1" x-show="sortField === 'client_fio' && sortDirection === 'desc'">↓</span>
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                        Скидка и лимит по поездке
                    </th><th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                        Фактические данные
                    </th>
                    
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                        Действия оператора
                    </th>
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
                            <div class="text-lg text-gray-900">
                                {{ $order->visit_data->format('H:i') }}
                            </div>
                            @if($order->visit_obratno)
                                <div class="text-sm font-medium text-gray-600 mt-1">
                                    Обратно: 
                                    <span class="text-lg">{{ $order->visit_obratno->format('H:i') }}</span>
                                </div>
                            @endif
                            {{-- --- Добавляем отображение имени группы --- --}}
                            @if($order->orderGroup) {{-- Проверяем, есть ли связанная группа --}}
                                <div class="mt-1">
                                    <span class="inline-block px-2 py-1 text-xs font-semibold bg-blue-100 text-blue-800 rounded-full">
                                        {{ $order->orderGroup->name }}
                                    </span>
                                </div>
                            @endif
                            {{-- --- Конец добавления группы--- --}}
                        @else
                            <div class="text-sm text-gray-500">-</div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        <div class="text-sm text-gray-900">
                            <span class="font-medium">Откуда:</span> {{ $order->adres_otkuda }}
                        </div>
                        <!-- Дополнительная информация об адресе "откуда" -->
                        @if($order->adres_otkuda_info)
                            <div class="text-xs text-gray-500 mt-1 ml-4">
                                {{ $order->adres_otkuda_info }}
                            </div>
                        @endif
                        <div class="text-sm text-gray-900 mt-1">
                            <span class="font-medium">Куда:</span> {{ $order->adres_kuda }}
                        </div>
                        <!-- Дополнительная информация об адресе "куда" -->
                        @if($order->adres_kuda_info)
                            <div class="text-xs text-gray-500 mt-1 ml-4">
                                {{ $order->adres_kuda_info }}
                            </div>
                        @endif
                        
                        @if($order->adres_obratno)
                            <div class="text-sm text-gray-900 mt-1">
                                <span class="font-medium">Обратно:</span> {{ $order->adres_obratno }}
                            </div>
                        @endif
                    </td>
                    <td class="px-6 py-4">
                        @if($order->client)
                        <div class="text-sm font-medium text-gray-900">
                            {{ $order->client->fio }}
                        </div>
                        @if($order->client_tel)
                        <div class="text-sm text-gray-600">
                            Тел: {{ $order->client_tel }}
                        </div>
                        @endif
                        @if($order->client_invalid)
                        <div class="text-sm text-gray-600">
                            Удостоверение: {{ $order->client_invalid }}
                        </div>
                        @endif
                        @if($order->client_sopr)
                        <div class="text-sm text-gray-600">
                            Сопровождающий: {{ $order->client_sopr }}
                        </div>
                        @endif
                        @if($order->category)
                        <div class="text-sm text-gray-600" title="Категория: {{ $order->category->name }}&#10;Скидка: {{ $order->category->skidka }}%&#10;Лимит: {{ $order->category->kol_p }} поездок/мес">
                            NMV: {{ $order->category->nmv }}
                        </div>
                        @endif
                        @if($order->dopus)
                        <div class="text-sm text-gray-900">
                            {{ $order->dopus->name }}
                        </div>
                        @endif
                        @if($order->client->rip_at)
                        <span class="inline-flex items-center px-2 py-0.5 rounded text-xs font-medium bg-gray-800 text-white mt-1">
                            RIP: {{ $order->client->rip_at->format('d.m.Y') }}
                        </span>
                        @endif
                        @else
                        <div class="text-sm text-gray-500">Клиент не найден</div>
                        @if($order->client_tel)
                        <div class="text-sm text-gray-600">
                            Тел: {{ $order->client_tel }}
                        </div>
                        @endif
                        @if($order->client_invalid)
                        <div class="text-sm text-gray-600">
                            Удостоверение: {{ $order->client_invalid }}
                        </div>
                        @endif
                        @if($order->client_sopr)
                        <div class="text-sm text-gray-600">
                            Сопровождающий: {{ $order->client_sopr }}
                        </div>
                        @endif
                        @if($order->category)
                        <div class="text-sm text-gray-600" title="Категория: {{ $order->category->name }}&#10;Скидка: {{ $order->category->skidka }}%&#10;Лимит: {{ $order->category->kol_p }} поездок/мес">
                            NMV: {{ $order->category->nmv }}
                        </div>
                        @endif
                        @if($order->dopus)
                        <div class="text-sm text-gray-600">
                            {{ $order->dopus->name }}
                        </div>
                        @endif
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
                        @if($order->taxi_way)
                            <div class="text-sm text-gray-900">
                                <span class="font-medium">Километраж:</span> {{number_format($order->taxi_way, 3, ',', ' ') . ' км' }}
                            </div>
                        @endif
                        @if($order->taxi_price)
                            <div class="text-sm text-gray-900 mt-1">
                                <span class="font-medium">Цена:</span> {{ number_format($order->taxi_price, 2, ',', ' ') . ' руб.' }}
                            </div>
                        @endif
                        @if($order->taxi_price - $order->taxi_vozm<>0)
                            <div class="text-sm text-gray-900 mt-1">
                                <span class="font-medium">К оплате:</span> 
                                {{ number_format($order->taxi_price - $order->taxi_vozm, 2, ',', ' ') . ' руб.' }}
                            </div>
                        @endif
                        @if($order->taxi_vozm)
                            <div class="text-sm text-gray-900 mt-1">
                                <span class="font-medium">К возмещению:</span> {{ $order->taxi_vozm ? number_format($order->taxi_vozm, 2, ',', ' ') . ' руб.' : '-' }}
                            </div>
                        @endif
                    </td>


                    <td class="px-6 py-4">
                        <div class="flex flex-col space-y-1">
                            <!-- Ссылка на просмотр заказа -->
                            <a href="{{ route('social-taxi-orders.show', array_merge(['social_taxi_order' => $order], $urlParams)) }}" class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-md hover:bg-blue-200 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Просмотр
                            </a>
                            @if(!$order->deleted_at)
                            @if($status->id == 1)
                            <a href="{{ route('social-taxi-orders.edit', array_merge(['social_taxi_order' => $order], $urlParams)) }}" class="inline-flex items-center px-3 py-1 bg-yellow-100 text-yellow-800 rounded-md hover:bg-yellow-200 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Редактировать
                            </a>
                            @else
                            <span class="inline-flex items-center px-3 py-1 bg-gray-300 text-gray-500 rounded-md text-sm" title="Редактирование возможно только для заказов со статусом 'Принят'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                </svg>
                                Редактировать
                            </span>
                            @endif
                            @endif

                            @if(!$order->deleted_at)
                            @if($status->id == 1)
                            <form action="{{ route('social-taxi-orders.destroy', array_merge(['social_taxi_order' => $order], $urlParams)) }}" method="POST" class="inline" onsubmit="return confirm('Вы уверены, что надо удалить заказ?')">
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
                            <button type="button" class="inline-flex items-center px-3 py-1 bg-gray-300 text-gray-500 rounded-md text-sm w-full cursor-not-allowed" disabled title="Удаление возможно только для заказов со статусом 'Принят'">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                </svg>
                                Удалить
                            </button>
                            @endif
                            @else
                            <form action="{{ route('social-taxi-orders.restore', array_merge(['social_taxi_order' => $order], $urlParams)) }}" method="POST" class="inline">
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
                            <!-- Кнопка копирования в actions.blade.php -->
                            @if(!$order->deleted_at && $status->id == 1)
                            <a href="{{ route('social-taxi-orders.create.by-type', array_merge(['type' => $order->type_order, 'copy_from' => $order->id], $urlParams)) }}" 
                               class="inline-flex items-center px-3 py-1 bg-green-100 text-green-800 rounded-md hover:bg-green-200 text-sm w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
                                </svg>
                                Копировать
                            </a>
                            @endif
                        </div>

                        <!-- Кнопка отмены заказа -->
                        @if(!$order->deleted_at && !$order->cancelled_at)
                        @php
                            $currentStatus = $order->currentStatus;
                            $statusId = $currentStatus ? $currentStatus->status_order_id : 1;
                        @endphp
                        @if($statusId == 1)
                            <a href="{{ route('social-taxi-orders.cancel.form', array_merge(['social_taxi_order' => $order], $urlParams)) }}" 
                               accesskey=""class="inline-flex items-center px-3 py-1 bg-red-100 text-red-800 rounded-md hover:bg-red-200 text-sm w-full">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                                </svg>
                                Отменить
                            </a>
                        @else
                            <button type="button" 
                                class="inline-flex items-center px-3 py-1 bg-gray-300 text-gray-500 rounded-md text-sm w-full cursor-not-allowed" 
                                disabled 
                                title="Отмена возможна только для заказов со статусом 'Принят'">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Отменить
                        </button>
                        @endif
                        @else
                        <button type="button" 
                                class="inline-flex items-center px-3 py-1 bg-gray-300 text-gray-500 rounded-md text-sm w-full cursor-not-allowed" 
                                disabled 
                                title="{{ $order->deleted_at ? 'Заказ удален' : 'Заказ уже отменен' }}">
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                            Отменить
                        </button>
                        @endif



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