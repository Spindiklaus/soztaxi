<x-app-layout>
    <div class="bg-gray-100 py-6">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <h1 class="text-3xl font-bold text-gray-800 mb-6">
                Заказы клиента: {{ $fiodtrn->fio }}
            </h1>
            
            <!-- Отображение применённых фильтров даты (опционально) -->
            @if(request('visit_date_from') || request('visit_date_to'))
            <div class="mb-4 p-3 bg-blue-50 rounded-lg border border-blue-200">
                <p class="text-sm text-blue-800">
                    <strong>Фильтр по дате поездки:</strong>
                    @if(request('visit_date_from'))
                        с {{ \Carbon\Carbon::parse(request('visit_date_from'))->format('d.m.Y') }}
                    @endif
                    @if(request('visit_date_to'))
                        по {{ \Carbon\Carbon::parse(request('visit_date_to'))->format('d.m.Y') }}
                    @endif
                </p>
            </div>
            @endif
            <!-- Конец блока фильтров -->

            <div class="bg-white shadow overflow-hidden sm:rounded-lg">
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200">
                        <thead class="bg-blue-800 text-gray-200">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                    №
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                    № заказа
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                    Дата поездки
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                    Маршрут
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                    Статус
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                    Цена / К возмещению
                                </th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-bold uppercase tracking-wider">
                                    Действия
                                </th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            @forelse ($orders as $order)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $orders->firstItem() + $loop->index }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                    {{ $order->pz_nom }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    {{ $order->visit_data ? $order->visit_data->format('d.m.Y H:i') : '-' }}
                                </td>
                                <td class="px-6 py-4 text-sm text-gray-900">
                                    <div><span class="font-medium">Откуда:</span> {{ $order->adres_otkuda }}</div>
                                    <div><span class="font-medium">Куда:</span> {{ $order->adres_kuda }}</div>
                                    @if($order->adres_obratno)
                                        <div><span class="font-medium">Обратно:</span> {{ $order->adres_obratno }}</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm">
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
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    @if($order->taxi_price)
                                    <div>Цена: {{ number_format($order->taxi_price, 2, ',', ' ') }} руб.</div>
                                    @endif
                                    @if($order->taxi_vozm)
                                    <div>Возмещение: {{ number_format($order->taxi_vozm, 2, ',', ' ') }} руб.</div>
                                    @endif
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                    <a href="{{ route('social-taxi-orders.show', $order) }}"
                                       class="inline-flex items-center px-3 py-1 bg-blue-100 text-blue-800 rounded-md hover:bg-blue-200 text-sm">
                                        Просмотр
                                    </a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="6" class="px-6 py-4 text-center text-gray-500">
                                    У клиента нет заказов
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Пагинация -->
            <div class="mt-6">
                {{ $orders->links() }}
            </div>
        </div>
    </div>
</x-app-layout>