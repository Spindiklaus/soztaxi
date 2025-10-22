<!-- resources/views/social-taxi-orders/taxi-components/table.blade.php -->
<div x-data="{
     sortField: '{{ $sort ?? 'visit_data' }}',
     sortDirection: '{{ $direction ?? 'asc' }}',
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
    <div class="overflow-x-auto">
        <table class="min-w-full divide-y divide-gray-200">
            <thead class="bg-blue-800 text-gray-200">
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
                    </th>
                    <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
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
                        @if($order->type_order == 1)
                            <div class="text-sm text-gray-900 mt-1">
                                <span class="font-medium">Предв. дальность:</span> {{ $order->predv_way }}км.
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
                            <!-- Только кнопка "Просмотр" для страницы такси -->
                            <a href="{{ route('social-taxi-orders.show', array_merge(['social_taxi_order' => $order, 'from_taxi_page' => 1], $urlParams)) }}" 
                               class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-md hover:bg-blue-200 text-sm">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                </svg>
                                Просмотр
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-6 py-4 text-center text-gray-500">
                        Заказы не найдены
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>