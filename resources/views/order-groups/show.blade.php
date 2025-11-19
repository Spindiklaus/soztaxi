<!-- resources/views/order_groups/show.blade.php -->

<x-app-layout>
    <div class="container mx-auto py-8">
        <div class="bg-white shadow-md rounded-lg overflow-hidden">
            <div class="px-6 py-4 border-b border-gray-200">
                <h2 class="text-2xl font-semibold text-gray-800 leading-tight">Детали Группы: {{ $orderGroup->name }}</h2>
                <div class="mt-2  flex justify-end">
                    <!-- Кнопка "Назад" -->
                    <a href="{{ route('order-groups.index') . '?' . http_build_query($urlParams) }}"
                       class="mb-0 inline-flex items-center px-4 py-2 bg-gray-100 text-gray-700 rounded-md hover:bg-gray-200 text-sm">
                        <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18" />
                        </svg>
                        Назад к списку
                    </a>
                </div>
            </div>
            <div class="p-2">
                <div class="mb-2">
<!--                    <p class="text-gray-700"><strong>ID:</strong> {{-- $orderGroup->id --}}</p>-->
<!--                    <p class="text-gray-700"><strong>Название:</strong> {{-- $orderGroup->name --}}</p>-->
                    <p class="text-gray-700">Дата начала поездки: <strong>{{ $orderGroup->visit_date->format('d.m.Y H:i') }}</strong></p>
<!--                    <p class="text-gray-700"><strong>Дата обновления:</strong> {{-- $orderGroup->updated_at->format('d.m.Y H:i:s') --}}</p>-->
                </div>

                <h3 class="text-xl font-medium text-gray-800 mb-4">Заказы в группе</h3>
                @if($orderGroup->orders->isNotEmpty())
                    <div class="overflow-x-auto">
                        <table class="w-full table-auto divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
    <!--                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID Заказа</th>-->
                                    <th scope="col" class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Клиент</th>
                                    <th scope="col" class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Время посадки</th>
                                    <th scope="col" class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Откуда</th>
                                    <th scope="col" class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Куда</th>
                                    <th scope="col" class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Предварительная дальность</th>
                                    <th scope="col" class="px-2 py-1 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Номер ПЗ</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($orderGroup->orders as $order)
                                    <tr>
    <!--                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{-- $order->id --}}</td>-->
                                        <td class="px-2 py-1 whitespace-nowrap text-sm text-gray-500">
                                            {{ $order->client ? $order->client->fio : 'N/A' }}
                                        </td>
                                        <td class="px-2 py-1 whitespace-nowrap text-sm text-gray-500">
                                            {{ $order->visit_data ? $order->visit_data->format('H:i') : 'N/A' }}                                       
                                        </td>
                                        <td class="px-2 py-1 whitespace-nowrap text-sm text-gray-500">
                                            {{ $order->adres_otkuda }}
                                            <!-- Дополнительная информация об адресе "откуда" -->
                                            @if($order->adres_otkuda_info)
                                                <div class="text-xs text-gray-500 mt-1 ml-4">
                                                    {{ $order->adres_otkuda_info }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-2 py-1 whitespace-nowrap text-sm text-gray-500">
                                            {{ $order->adres_kuda }}
                                            <!-- Дополнительная информация об адресе "куда" -->
                                            @if($order->adres_kuda_info)
                                                <div class="text-xs text-gray-500 mt-1 ml-4">
                                                    {{ $order->adres_kuda_info }}
                                                </div>
                                            @endif
                                        </td>
                                        <td class="px-2 py-1 whitespace-nowrap text-sm text-gray-500">
                                            {{ $order->predv_way }}
                                        </td>
                                        <td class="px-2 py-1 whitespace-nowrap text-sm font-medium text-gray-900">
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
                                            @else <!-- не удален -->
                                                <div class="text-sm text-gray-500 {{ $colorClass }}" title="{{ $status->name }}">
                                                    {{ $order->pz_nom }} от {{ $order->pz_data->format('d.m.Y H:i') }}
                                                </div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>    
                @else
                    <p class="text-gray-700">В этой группе пока нет заказов.</p>
                @endif
            </div>
        </div>
    </div>
</x-app-layout>